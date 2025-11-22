(function( $, wp ) {
    'use strict';

    if ( ! wp || ! wp.media || ! window.FOOGALLERY_MEDIA_FOLDERS ) {
        return;
    }

    var settings = window.FOOGALLERY_MEDIA_FOLDERS;

    settings.terms   = settings.terms || [];
    settings.strings = $.extend( {
        foldersHeading: 'Folders',
        allFolders: 'All Folders',
        unassignLabel: 'Unassign',
        assigning: 'Assigning...',
        assignmentSuccess: 'Folder assignment saved.',
        assignmentFailure: 'Could not update folder. Please try again.',
        dragToFolder: 'Drag selected items to a folder to assign it.',
        helpHtml: '',
    }, settings.strings || {} );

    var MediaFolders = {
        draggedIds: [],
        draggedFolderId: 0,
        draggedFolderParent: 0,
        apiRequest: function( args ) {
            if ( wp.apiRequest ) {
                return wp.apiRequest( args );
            }

            return $.Deferred().reject( { message: settings.strings.assignmentFailure } ).promise();
        },

        setDraggedIds: function( ids ) {
            this.draggedIds = ids;
        },

        setDraggedFolder: function( id ) {
            this.draggedFolderId = id || 0;
        },

        setDraggedFolderParent: function( id ) {
            this.draggedFolderParent = id || 0;
        },

        getDraggedIds: function( selection, fallbackId ) {
            if ( selection && selection.length ) {
                return selection.pluck( 'id' );
            }

            if ( fallbackId ) {
                return [ fallbackId ];
            }

            return this.draggedIds.slice( 0 );
        },

        ensureAttachmentsDraggable: function( browser ) {
            if ( ! browser || ! browser.$el ) {
                return;
            }

            var $frame = browser.controller && browser.controller.$el ? browser.controller.$el : browser.$el.closest( '.media-frame' );
            var dragPreview;

            browser.$el.on( 'mouseenter', '.attachment', function() {
                var $item = $( this );
                if ( !$item.attr( 'draggable' ) ) {
                    $item.attr( 'draggable', true );
                }
            } );

            browser.$el.on( 'dragstart', '.attachment', function( event ) {
                var $item      = $( this );
                var attachment = parseInt( $item.data( 'id' ), 10 );
                var selection  = browser.controller && browser.controller.state ? browser.controller.state().get( 'selection' ) : false;
                var ids        = MediaFolders.getDraggedIds( selection, attachment );
                var currentFolder = 0;
                if ( browser.controller && browser.controller.state ) {
                    var lib = browser.controller.state().get( 'library' );
                    currentFolder = lib && lib.props ? lib.props.get( 'foogallery_folder' ) : 0;
                }

                MediaFolders.setDraggedIds( ids );

                if ( $frame && $frame.length ) {
                    $frame.addClass( 'foogallery-attachment-dragging' );
                    if ( currentFolder ) {
                        $frame.find( '.foogallery-folder-label.is-root' ).text( settings.strings.unassignLabel );
                    }
                }

                // Provide a custom drag image (thumbnail) to avoid capturing the whole UI.
                if ( event.originalEvent && event.originalEvent.dataTransfer ) {
                    var $thumb = $item.find( 'img' ).first();
                    if ( $thumb.length ) {
                        dragPreview = $thumb.clone().css( {
                            position: 'absolute',
                            top: '-9999px',
                            left: '-9999px',
                            width: '80px',
                            height: 'auto',
                            pointerEvents: 'none',
                        } ).appendTo( document.body );
                        event.originalEvent.dataTransfer.setDragImage( dragPreview[0], 10, 10 );
                    }
                }

                if ( event.originalEvent && event.originalEvent.dataTransfer ) {
                    event.originalEvent.dataTransfer.effectAllowed = 'move';
                    event.originalEvent.dataTransfer.setData( 'text/plain', ids.join( ',' ) );
                }
            } );

            browser.$el.on( 'dragend', '.attachment', function() {
                if ( $frame && $frame.length ) {
                    $frame.removeClass( 'foogallery-attachment-dragging' );
                    $frame.find( '.foogallery-folder-label.is-root' ).text( settings.strings.allFolders );
                }

                if ( dragPreview && dragPreview.length ) {
                    dragPreview.remove();
                    dragPreview = null;
                }
            } );
        },
    };

    var FolderTreeView = wp.media.View.extend( {
        className: 'foogallery-media-folders-wrapper',

        events: {
            'click .foogallery-folder-label': 'onFolderClick',
            'click .foogallery-folder-row': 'onRowClick',
            'keydown .foogallery-folder-label': 'onFolderKeydown',
            'dragover li.foogallery-folder-node': 'onDragOver',
            'dragleave li.foogallery-folder-node': 'onDragLeave',
            'drop li.foogallery-folder-node': 'onDrop',
            'dragover .foogallery-folder-dropzone': 'onDropzoneOver',
            'dragleave .foogallery-folder-dropzone': 'onDropzoneLeave',
            'drop .foogallery-folder-dropzone': 'onDropzoneDrop',
            'dragstart .foogallery-folder-row': 'onFolderDragStart',
            'dragend .foogallery-folder-row': 'onFolderDragEnd',
            'click .foogallery-manage-folders': 'toggleManageMode',
            'click .foogallery-add-folder': 'startAddFolder',
            'click .foogallery-folder-edit': 'startRename',
            'click .foogallery-folder-edit-cancel': 'cancelRename',
            'click .foogallery-folder-edit-save': 'saveRename',
            'click .foogallery-folder-delete': 'deleteFolder',
            'click .foogallery-folder-new-save': 'saveNewFolder',
            'click .foogallery-folder-new-cancel': 'cancelAddFolder',
            'click .foogallery-folder-help-toggle': 'toggleHelp',
        },

        initialize: function( options ) {
            this.controller = options.controller;
            this.library    = options.library;
            this.selected   = 0;
            this.manageMode = false;
            this.editingId  = null;
            this.addingNew  = false;
            this.terms      = settings.terms || [];
            this.termLookup = this.buildTermLookup( this.terms );
        },

        buildTermLookup: function( terms ) {
            var lookup = {};
            terms.forEach( function( term ) {
                lookup[ term.parent ] = lookup[ term.parent ] || [];
                lookup[ term.parent ].push( term );
            } );
            Object.keys( lookup ).forEach( function( parent ) {
                lookup[ parent ].sort( function( a, b ) {
                    var ao = typeof a.order === 'number' ? a.order : 0;
                    var bo = typeof b.order === 'number' ? b.order : 0;
                    if ( ao !== bo ) {
                        return ao - bo;
                    }
                    return a.name.localeCompare( b.name );
                } );
            } );
            return lookup;
        },

        render: function() {
            this.$el.empty();
            var headerHtml = '' +
                '<div class="foogallery-media-folders-header">' +
                    '<span class="foogallery-folder-title">' + settings.strings.foldersHeading + '</span>' +
                    '<button type="button" class="foogallery-folder-help-toggle" aria-expanded="false" aria-controls="foogallery-folder-help"><span class="dashicons dashicons-editor-help" aria-hidden="true"></span><span class="screen-reader-text">Toggle folder help</span></button>' +
                    '<span class="foogallery-media-folders-status" aria-live="polite"></span>' +
                '</div>' +
                '<div id="foogallery-folder-help" class="foogallery-folder-help" hidden>' + settings.strings.helpHtml + '</div>';

            this.$el.append( headerHtml );
            this.$el.append( this.renderList( 0, true ) );
            this.highlightSelection();
            return this;
        },

        renderList: function( parentId, isRoot ) {
            var $list = $( '<ul />', {
                'class': isRoot ? 'foogallery-media-folders' : 'foogallery-media-folders-children',
                'role': isRoot ? 'tree' : 'group',
            } );

            if ( isRoot ) {
                $list.append( this.renderNode( { id: 0, name: settings.strings.allFolders, parent: 0 }, true ) );
            if ( this.addingNew ) {
                $list.append( this.renderNewFolderRow() );
            }
        }

        var children = this.termLookup[ parentId ] || [];

        children.forEach( function( term ) {
            if ( this.manageMode ) {
                $list.append( this.renderDropzone( parentId, term.id, 'before' ) );
            }

            var $node = this.renderNode( term, false );
            var $children = this.renderList( term.id, false );
            if ( $children.children().length ) {
                $node.append( $children );
            }
            $list.append( $node );
        }.bind( this ) );

        return $list;
    },

        renderNode: function( term, isRoot ) {
            var $node = $( '<li />', {
                'class': 'foogallery-folder-node foogallery-folder-node-' + term.id + ' foogallery-folder-node-level-' + ( isRoot ? 0 : 1 ),
                'data-folder-id': term.id,
                'role': 'treeitem',
                'tabindex': 0,
            } );

            var $labelWrapper = $( '<div />', { 'class': 'foogallery-folder-row' } );
            var $label = $( '<a />', {
                'class': 'foogallery-folder-label',
                'href': '#',
                'data-folder-id': term.id,
                'text': term.name
            } );

            if ( isRoot ) {
                $label.addClass( 'is-root' );
            }

            var $labelContainer = $( '<span class="foogallery-folder-label-container" />' );
            if ( this.manageMode && ! isRoot ) {
                $labelContainer.append( '<span class="foogallery-folder-drag-handle dashicons dashicons-menu-alt2"></span>' );
            }
            $labelContainer.append( $label );
            var count = parseInt( term.count, 10 );
            if ( ! isNaN( count ) ) {
                var $pill = $( '<span class="foogallery-folder-count" />' ).text( count );
                $labelContainer.append( $pill );
            }
            $labelWrapper.append( $labelContainer );

            if ( this.manageMode && ! isRoot ) {
                $labelWrapper.attr( 'draggable', true );
            } else {
                $labelWrapper.removeAttr( 'draggable' );
            }

            // Manage actions.
            if ( isRoot ) {
                var $actions = $( '<span class="foogallery-folder-actions" />' );
                var $addBtn    = $( '<button type="button" class="foogallery-add-folder" aria-label="Add folder"><span class="dashicons dashicons-plus-alt2"></span></button>' );
                var $manageBtn = $( '<button type="button" class="foogallery-manage-folders" aria-label="Manage folders"><span class="dashicons dashicons-admin-generic"></span></button>' );
                $actions.append( $addBtn ).append( $manageBtn );
                $labelWrapper.append( $actions );
            } else if ( this.manageMode ) {
                var $controlGroup = $( '<span class="foogallery-folder-actions" />' );

                if ( this.editingId === term.id ) {
                    var $inputWrap = $( '<span class="foogallery-folder-edit-wrap" />' );
                    var $input = $( '<input type="text" class="foogallery-folder-edit-input" />', { value: term.name } );
                    // Explicitly set value property to avoid any attr/prop mismatch.
                    $input.val( term.name || '' );
                    $inputWrap.append( $input );
                    $labelContainer.addClass( 'is-editing' ).empty().append( $inputWrap );

                    var $save  = $( '<button type="button" class="foogallery-folder-edit-save" data-folder-id="' + term.id + '" aria-label="Save folder name"><span class="dashicons dashicons-yes"></span></button>' );
                    var $cancel= $( '<button type="button" class="foogallery-folder-edit-cancel" data-folder-id="' + term.id + '" aria-label="Cancel rename"><span class="dashicons dashicons-no"></span></button>' );
                    var $delete= $( '<button type="button" class="foogallery-folder-delete" data-folder-id="' + term.id + '" aria-label="Delete folder"><span class="dashicons dashicons-trash"></span></button>' );
                    $controlGroup.append( $save, $cancel, $delete );
                } else {
                    var $edit   = $( '<button type="button" class="foogallery-folder-edit" data-folder-id="' + term.id + '" aria-label="Rename folder"><span class="dashicons dashicons-edit"></span></button>' );
                    var $deleteB= $( '<button type="button" class="foogallery-folder-delete" data-folder-id="' + term.id + '" aria-label="Delete folder"><span class="dashicons dashicons-trash"></span></button>' );
                    $controlGroup.append( $edit, $deleteB );
                }

                $labelWrapper.append( $controlGroup );
            }

            $node.append( $labelWrapper );
            return $node;
        },

        renderDropzone: function( parentId, siblingId, position ) {
            return $( '<div class="foogallery-folder-dropzone" data-parent-id="' + parentId + '" data-sibling-id="' + siblingId + '" data-position="' + position + '"></div>' );
        },

        renderNewFolderRow: function() {
            var $node = $( '<li class="foogallery-folder-node foogallery-folder-node-new" />' );
            var $row  = $( '<div class="foogallery-folder-row" />' );
            var $input = $( '<input type="text" class="foogallery-folder-new-input" placeholder="New folder name" />' );
            var $actions = $( '<span class="foogallery-folder-actions" />' );
            var $save = $( '<button type="button" class="foogallery-folder-new-save" aria-label="Save new folder"><span class="dashicons dashicons-yes" aria-hidden="true"></span></button>' );
            var $cancel = $( '<button type="button" class="foogallery-folder-new-cancel" aria-label="Cancel add folder"><span class="dashicons dashicons-no" aria-hidden="true"></span></button>' );
            $actions.append( $save, $cancel );
            $row.append( $input ).append( $actions );
            $node.append( $row );
            return $node;
        },

        onFolderClick: function( event ) {
            event.preventDefault();
            var folderId = parseInt( $( event.currentTarget ).data( 'folderId' ), 10 );
            this.setFolder( isNaN( folderId ) ? 0 : folderId );
        },

        onRowClick: function( event ) {
            // Ignore clicks on action buttons/input controls.
            if ( $( event.target ).closest( 'button, input' ).length ) {
                return;
            }
            var $row = $( event.currentTarget );
            var folderId = parseInt( $row.find( '.foogallery-folder-label' ).data( 'folderId' ), 10 );
            this.setFolder( isNaN( folderId ) ? 0 : folderId );
        },

        toggleHelp: function( event ) {
            event.preventDefault();
            var $btn = $( event.currentTarget );
            var $help = this.$el.find( '#foogallery-folder-help' );
            var isOpen = $help.is( ':visible' );
            if ( isOpen ) {
                $help.attr( 'hidden', 'hidden' );
                $btn.attr( 'aria-expanded', 'false' );
            } else {
                $help.removeAttr( 'hidden' );
                $btn.attr( 'aria-expanded', 'true' );
            }
        },

        onFolderKeydown: function( event ) {
            if ( event.key === 'Enter' || event.key === ' ' ) {
                this.onFolderClick( event );
            }
        },

        onDragOver: function( event ) {
            event.preventDefault();
            event.stopPropagation();
            $( event.currentTarget ).addClass( 'is-drag-over' );
            if ( event.originalEvent && event.originalEvent.dataTransfer ) {
                event.originalEvent.dataTransfer.dropEffect = 'move';
            }
        },

        onDragLeave: function( event ) {
            event.stopPropagation();
            $( event.currentTarget ).removeClass( 'is-drag-over' );
        },

        onDrop: function( event ) {
            event.preventDefault();
            event.stopPropagation();
            var $target  = $( event.currentTarget );
            var folderId = parseInt( $target.data( 'folderId' ), 10 );
            var currentFolder = this.library && this.library.props ? this.library.props.get( 'foogallery_folder' ) : this.selected || 0;
            var isFolderDrag = this.manageMode && MediaFolders.draggedFolderId;
            var targetTerm = this.getTermById( folderId );

            // Show spinner on the target while assigning.
            var $row = $target.children( '.foogallery-folder-row' );
            var $rowSpinner = $row.find( '.foogallery-folder-row-spinner' );
            if ( ! $rowSpinner.length ) {
                $rowSpinner = $( '<span class="foogallery-folder-row-spinner spinner is-active"></span>' ).appendTo( $row );
            } else {
                $rowSpinner.addClass( 'is-active' ).show();
            }

            $target.removeClass( 'is-drag-over' );

            if ( isFolderDrag ) {
                var movingId = MediaFolders.draggedFolderId;
                if ( movingId === folderId || this.isDescendant( movingId, folderId ) ) {
                    $rowSpinner.removeClass( 'is-active' ).hide();
                    return;
                }
                // Nest under the target folder.
                this.moveFolder( movingId, folderId, $rowSpinner );
                return;
            }

            if ( ! settings.canAssign || ! folderId ) {
                if ( folderId === 0 && currentFolder ) {
                    // Allow unassign via All Folders only when a folder is selected.
                } else {
                    $rowSpinner.removeClass( 'is-active' ).hide();
                    return;
                }
            }

            var selection = this.controller && this.controller.state ? this.controller.state().get( 'selection' ) : false;
            var ids       = MediaFolders.getDraggedIds( selection );

            if ( ! ids.length && event.originalEvent && event.originalEvent.dataTransfer ) {
                var data = event.originalEvent.dataTransfer.getData( 'text/plain' );
                ids      = data ? data.split( ',' ).map( function( id ) { return parseInt( id, 10 ); } ) : [];
                ids      = ids.filter( function( id ) { return !! id; } );
            }

            if ( ! ids.length ) {
                $rowSpinner.removeClass( 'is-active' ).hide();
                return;
            }

            this.assignToFolder( ids, folderId, $rowSpinner );
        },

        onDropzoneOver: function( event ) {
            event.preventDefault();
            event.stopPropagation();
            var $zone = $( event.currentTarget );
            if ( ! ( this.manageMode && MediaFolders.draggedFolderId ) ) {
                return;
            }
            $zone.addClass( 'is-active' );
        },

        onDropzoneLeave: function( event ) {
            event.stopPropagation();
            $( event.currentTarget ).removeClass( 'is-active' );
        },

        onDropzoneDrop: function( event ) {
            event.preventDefault();
            event.stopPropagation();
            var $zone = $( event.currentTarget );
            $zone.removeClass( 'is-active' );

            if ( ! ( this.manageMode && MediaFolders.draggedFolderId ) ) {
                return;
            }

            var parentId = parseInt( $zone.data( 'parentId' ), 10 );
            var siblingId = parseInt( $zone.data( 'siblingId' ), 10 );
            var position = $zone.data( 'position' ) || 'before';
            var movingId = MediaFolders.draggedFolderId;
            var movingParent = MediaFolders.draggedFolderParent;

            if ( parentId !== movingParent ) {
                // If parent differs, treat as nesting under new parent.
                this.moveFolder( movingId, parentId, null );
                return;
            }

            if ( siblingId === movingId ) {
                return;
            }

            this.reorderSiblings( parentId, movingId, siblingId, position, null );
        },

        onFolderDragStart: function( event ) {
            if ( ! this.manageMode ) {
                return;
            }
            var folderId = parseInt( $( event.currentTarget ).find( '.foogallery-folder-label' ).data( 'folderId' ), 10 );
            if ( ! folderId ) {
                return;
            }
            MediaFolders.setDraggedFolder( folderId );
            var term = this.getTermById( folderId );
            MediaFolders.setDraggedFolderParent( term ? term.parent : 0 );
            if ( event.originalEvent && event.originalEvent.dataTransfer ) {
                event.originalEvent.dataTransfer.effectAllowed = 'move';
                event.originalEvent.dataTransfer.setData( 'text/plain', 'folder:' + folderId );
            }
        },

        onFolderDragEnd: function() {
            MediaFolders.setDraggedFolder( 0 );
            MediaFolders.setDraggedFolderParent( 0 );
            this.$el.find( '.is-drag-over' ).removeClass( 'is-drag-over' );
        },

        getTermById: function( id ) {
            id = parseInt( id, 10 );
            for ( var i = 0; i < this.terms.length; i++ ) {
                if ( this.terms[ i ].id === id ) {
                    return this.terms[ i ];
                }
            }
            return null;
        },

        isDescendant: function( parentId, maybeChildId ) {
            var children = this.termLookup[ parentId ] || [];
            for ( var i = 0; i < children.length; i++ ) {
                if ( children[ i ].id === maybeChildId ) {
                    return true;
                }
                if ( this.isDescendant( children[ i ].id, maybeChildId ) ) {
                    return true;
                }
            }
            return false;
        },

        moveFolder: function( folderId, newParentId, $rowSpinner ) {
            this.setStatus( 'Moving folder...' );
            MediaFolders.apiRequest( {
                path: '/wp/v2/' + settings.taxonomy + '/' + folderId,
                method: 'POST',
                data: { parent: newParentId },
            } ).done( function( term ) {
                this.terms = this.terms.map( function( t ) {
                    if ( t.id === folderId ) {
                        t.parent = term && typeof term.parent !== 'undefined' ? term.parent : newParentId;
                        t.order = 0;
                    }
                    return t;
                } );
                this.termLookup = this.buildTermLookup( this.terms );
                this.render();
                this.setStatus( 'Folder moved.' );
            }.bind( this ) ).fail( function() {
                this.setStatus( 'Could not move folder.' );
            }.bind( this ) ).always( function() {
                MediaFolders.setDraggedFolder( 0 );
                if ( $rowSpinner ) {
                    $rowSpinner.removeClass( 'is-active' ).hide();
                }
            } );
        },

        reorderSiblings: function( parentId, movingId, targetId, position, $rowSpinner ) {
            var siblings = ( this.termLookup[ parentId ] || [] ).slice( 0 );
            var filtered = siblings.filter( function( t ) { return t.id !== movingId; } );
            var newOrder = [];

            if ( targetId === 0 ) {
                newOrder = filtered.map( function( t ) { return t.id; } );
                newOrder.push( movingId );
            } else {
                for ( var i = 0; i < filtered.length; i++ ) {
                    var sibId = filtered[ i ].id;
                    if ( sibId === targetId && position === 'before' ) {
                        newOrder.push( movingId );
                    }
                    newOrder.push( sibId );
                    if ( sibId === targetId && position === 'after' ) {
                        newOrder.push( movingId );
                    }
                }
                if ( newOrder.indexOf( movingId ) === -1 ) {
                    newOrder.push( movingId );
                }
            }

            var self = this;
            this.setStatus( 'Reordering...' );
            $.post( settings.ajaxUrl, {
                action: 'foogallery_reorder_media_categories',
                nonce: settings.nonce,
                parent_id: parentId,
                ordered_ids: newOrder,
            } ).done( function() {
                // Update local orders to match newOrder.
                self.terms = self.terms.map( function( t ) {
                    if ( newOrder.indexOf( t.id ) !== -1 ) {
                        t.order = newOrder.indexOf( t.id );
                    }
                    return t;
                } );
                self.termLookup = self.buildTermLookup( self.terms );
                self.render();
                self.setStatus( 'Folder reordered.' );
            } ).fail( function() {
                self.setStatus( 'Could not reorder folder.' );
            } ).always( function() {
                MediaFolders.setDraggedFolder( 0 );
                MediaFolders.setDraggedFolderParent( 0 );
                if ( $rowSpinner ) {
                    $rowSpinner.removeClass( 'is-active' ).hide();
                }
            } );
        },

        toggleManageMode: function( event ) {
            event.preventDefault();
            this.manageMode = ! this.manageMode;
            this.addingNew  = false;
            this.editingId  = null;
            this.render();
        },

        startAddFolder: function( event ) {
            event.preventDefault();
            this.addingNew  = true;
            this.editingId  = null;
            this.render();
            this.$el.find( '.foogallery-folder-new-input' ).focus();
        },

        cancelAddFolder: function( event ) {
            if ( event ) {
                event.preventDefault();
            }
            this.addingNew = false;
            this.render();
        },

        startRename: function( event ) {
            event.preventDefault();
            var folderId = parseInt( $( event.currentTarget ).data( 'folderId' ), 10 );
            this.manageMode = true;
            this.editingId  = folderId;
            this.render();
            this.$el.find( '.foogallery-folder-edit-input' ).focus();
        },

        cancelRename: function( event ) {
            event.preventDefault();
            this.editingId = null;
            this.render();
        },

        saveRename: function( event ) {
            event.preventDefault();
            var folderId = parseInt( $( event.currentTarget ).data( 'folderId' ), 10 );
            var newName  = $( event.currentTarget ).closest( '.foogallery-folder-row' ).find( '.foogallery-folder-edit-input' ).val();
            this.renameFolder( folderId, newName );
        },

        saveNewFolder: function( event ) {
            event.preventDefault();
            var $row = $( event.currentTarget ).closest( '.foogallery-folder-row' );
            var name = $row.find( '.foogallery-folder-new-input' ).val();
            this.createFolder( name, 0 );
        },

        deleteFolder: function( event ) {
            event.preventDefault();
            var folderId = parseInt( $( event.currentTarget ).data( 'folderId' ), 10 );
            if ( ! folderId ) {
                return;
            }

            if ( ! window.confirm( 'Delete this folder and its children?' ) ) {
                return;
            }

            this.removeFolder( folderId );
        },

        setStatus: function( message ) {
            this.$el.find( '.foogallery-media-folders-status' ).html( message || '' );
        },

        renameFolder: function( folderId, newName ) {
            newName = ( newName || '' ).trim();
            if ( ! folderId || ! newName ) {
                this.setStatus( 'A folder name is required.' );
                return;
            }

            this.setStatus( 'Saving...' );
            MediaFolders.apiRequest( {
                path: '/wp/v2/' + settings.taxonomy + '/' + folderId,
                method: 'POST',
                data: { name: newName },
            } ).done( function( term ) {
                this.terms = this.terms.map( function( t ) {
                    if ( t.id === folderId ) {
                        t.name = term && term.name ? term.name : newName;
                    }
                    return t;
                } );
                this.termLookup = this.buildTermLookup( this.terms );
                this.editingId = null;
                this.render();
                this.setStatus( 'Folder renamed.' );
            }.bind( this ) ).fail( function() {
                this.setStatus( 'Could not rename folder.' );
            }.bind( this ) );
        },

        createFolder: function( name, parentId ) {
            name = ( name || '' ).trim();
            if ( ! name ) {
                this.setStatus( 'A folder name is required.' );
                return;
            }

            this.setStatus( 'Saving...' );
            MediaFolders.apiRequest( {
                path: '/wp/v2/' + settings.taxonomy,
                method: 'POST',
                data: { name: name, parent: parentId || 0 },
            } ).done( function( term ) {
                if ( term && term.id ) {
                    this.terms.unshift( {
                        id: term.id,
                        name: term.name,
                        parent: term.parent || 0,
                    } );
                }
                this.termLookup = this.buildTermLookup( this.terms );
                this.addingNew = false;
                this.render();
                this.setStatus( 'Folder created.' );
            }.bind( this ) ).fail( function() {
                this.setStatus( 'Could not create folder.' );
            }.bind( this ) );
        },

        collectDescendants: function( folderId, list ) {
            list = list || [];
            var children = this.termLookup[ folderId ] || [];
            children.forEach( function( child ) {
                list.push( child.id );
                this.collectDescendants( child.id, list );
            }.bind( this ) );
            return list;
        },

        removeFolder: function( folderId ) {
            this.setStatus( 'Deleting...' );
            MediaFolders.apiRequest( {
                path: '/wp/v2/' + settings.taxonomy + '/' + folderId + '?force=true',
                method: 'DELETE',
            } ).done( function() {
                var toRemove = this.collectDescendants( folderId, [ folderId ] );
                this.terms = this.terms.filter( function( term ) {
                    return toRemove.indexOf( term.id ) === -1;
                } );
                this.termLookup = this.buildTermLookup( this.terms );
                if ( this.selected && toRemove.indexOf( this.selected ) !== -1 ) {
                    this.selected = 0;
                }
                this.render();
                this.setStatus( 'Folder deleted.' );
            }.bind( this ) ).fail( function() {
                this.setStatus( 'Could not delete folder.' );
            }.bind( this ) );
        },

        assignToFolder: function( ids, folderId, $rowSpinner ) {
            var $status = this.$el.find( '.foogallery-media-folders-status' );
            var currentFolder = this.library && this.library.props ? this.library.props.get( 'foogallery_folder' ) : this.selected || 0;
            var isUnassign = folderId === 0;

            $status.text( isUnassign ? 'Unassigning...' : settings.strings.assigning );

            wp.ajax.post( 'foogallery_assign_media_categories', {
                term_id: folderId,
                attachment_ids: ids,
                nonce: settings.nonce,
                source_term_id: currentFolder,
            } ).done( function() {
                var countMsg = ids.length + ' item(s) ' + ( folderId === 0 ? 'unassigned' : 'assigned' );
                $status.text( countMsg );

                // Restore the previous folder filter/selection after assignment.
                if ( this.library && this.library.props ) {
                    this.library.props.set( 'foogallery_folder', currentFolder );
                }
                this.selected = currentFolder;
                this.updateCountsAfterAssign( currentFolder, folderId, ids.length );
                this.highlightSelection();

                // If we unassigned while viewing a folder, remove those items from the current collection immediately.
                if ( folderId === 0 && currentFolder && this.library && typeof this.library.remove === 'function' ) {
                    var toRemove = this.library.filter( function( model ) {
                        return ids.indexOf( model.get( 'id' ) ) !== -1;
                    } );
                    if ( toRemove.length ) {
                        this.library.remove( toRemove );
                    }
                }

                var more = this.library.more();
                if ( more && typeof more.then === 'function' ) {
                    more.then( function() {
                        this.controller.trigger( 'selection:action:attachment:update' );
                    }.bind( this ) );
                } else {
                    this.controller.trigger( 'selection:action:attachment:update' );
                }

                if ( $rowSpinner ) {
                    $rowSpinner.removeClass( 'is-active' ).hide();
                }
            }.bind( this ) ).fail( function() {
                $status.text( settings.strings.assignmentFailure );
                if ( $rowSpinner ) {
                    $rowSpinner.removeClass( 'is-active' ).hide();
                }
            } );
        },

        updateCountsAfterAssign: function( sourceId, targetId, delta ) {
            delta = delta || 0;
            if ( delta <= 0 ) {
                return;
            }

            var changed = false;
            if ( sourceId > 0 ) {
                this.terms = this.terms.map( function( t ) {
                    if ( t.id === sourceId ) {
                        t.count = Math.max( 0, ( parseInt( t.count, 10 ) || 0 ) - delta );
                        changed = true;
                    }
                    return t;
                } );
            }

            if ( targetId > 0 ) {
                this.terms = this.terms.map( function( t ) {
                    if ( t.id === targetId ) {
                        t.count = ( parseInt( t.count, 10 ) || 0 ) + delta;
                        changed = true;
                    }
                    return t;
                } );
            }

            if ( changed ) {
                this.termLookup = this.buildTermLookup( this.terms );
                this.render();
            }
        },

        highlightSelection: function() {
            this.$el.find( '.foogallery-folder-node' ).removeClass( 'is-active' );
            this.$el.find( '.foogallery-folder-node-' + this.selected ).addClass( 'is-active' );
            this.$el.find( '.foogallery-folder-label' ).attr( 'aria-selected', 'false' );
            this.$el.find( '.foogallery-folder-node-' + this.selected + ' > .foogallery-folder-label' ).attr( 'aria-selected', 'true' );
        },

        setFolder: function( folderId ) {
            this.selected = folderId;
            this.highlightSelection();

            // Clear selection when switching folders to avoid stale selections outside the new filter.
            var selection = this.controller && this.controller.state ? this.controller.state().get( 'selection' ) : null;
            if ( selection && selection.length ) {
                selection.reset();
            }

            this.library.props.set( 'foogallery_folder', folderId );
            var more = this.library.more();
            if ( more && typeof more.then === 'function' ) {
                more.then( function() {
                    this.controller.trigger( 'selection:action:attachment:update' );
                }.bind( this ) );
            } else {
                this.controller.trigger( 'selection:action:attachment:update' );
            }
        },
    } );

    var FolderAttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend( {
        initialize: function() {
            wp.media.view.AttachmentsBrowser.prototype.initialize.apply( this, arguments );

            if ( this.controller && this.controller.$el ) {
                this.controller.$el.find( '.media-frame-content' ).addClass( 'foogallery-has-folders' );
            }

            MediaFolders.ensureAttachmentsDraggable( this );
        },

        createSidebar: function() {
            wp.media.view.AttachmentsBrowser.prototype.createSidebar.apply( this, arguments );

            var folderTree = new FolderTreeView( {
                controller: this.controller,
                library: this.options.controller.state().get( 'library' ),
            } );

            this.views.add( folderTree );

            // Wrap the folders and attachments together so they sit beside each other.
            _.defer( function() {
                if ( this.$el.find( '.foogallery-browser-with-folders' ).length ) {
                    return;
                }

                var $attachmentsWrapper = this.$el.find( '.attachments-wrapper' ).first();
                if ( ! $attachmentsWrapper.length ) {
                    return;
                }

                var $container = $( '<div class="foogallery-browser-with-folders" />' );
                $attachmentsWrapper.before( $container );
                $container.append( folderTree.$el );
                $container.append( $attachmentsWrapper );

                if ( this.controller && this.controller.$el ) {
                    this.controller.$el.find( '.media-frame-content' ).addClass( 'foogallery-has-folders' );
                }

                MediaFolders.ensureAttachmentsDraggable( this );
            }.bind( this ) );
        },

        toggleServerRendering: function() {
            wp.media.view.AttachmentsBrowser.prototype.toggleServerRendering.apply( this, arguments );
            MediaFolders.ensureAttachmentsDraggable( this );
        },
    } );

    // Keep a reference to the original MediaFrame.Post so our overrides don't recurse.
    var OriginalMediaFramePost = wp.media.view.MediaFrame.Post;

    var FolderMediaFrame = OriginalMediaFramePost.extend( {
        browseRouter: function( routerView ) {
            routerView.set( { library: { text: wp.media.view.l10n.mediaLibraryTitle } } );
        },

        bindHandlers: function() {
            OriginalMediaFramePost.prototype.bindHandlers.apply( this, arguments );
            this.on( 'router:create:browse', this.createRouter, this );
            this.on( 'content:create:browse', this.browseContent, this );
        },

        browseContent: function( region ) {
            var state    = this.state();
            var options  = this.options;
            var library  = state.get( 'library' );
            var view = new FolderAttachmentsBrowser( {
                controller: this,
                collection: library,
                selection: state.get( 'selection' ),
                model: state,
                scrollElement: document,
                autoSelect: true,
                suggestedWidth: options.suggestedWidth,
                suggestedHeight: options.suggestedHeight,
            } );

            region.view = view;
        },
    } );

    var open = wp.media.editor.open;
    wp.media.editor.open = function( id, options ) {
        options = options || {};
        options.frame = FolderMediaFrame;
        return open.apply( wp.media.editor, [ id, options ] );
    };

    wp.media.view.MediaFrame.Post = FolderMediaFrame;

    // Override the Select frame to inject folder support for FooGallery-owned modals.
    var OriginalMediaFrameSelect = wp.media.view.MediaFrame.Select;

    var FolderMediaFrameSelect = OriginalMediaFrameSelect.extend( {
        browseContent: function( region ) {
            var state    = this.state();
            var options  = this.options;
            var library  = state.get( 'library' );

            var view = new FolderAttachmentsBrowser( {
                controller: this,
                collection: library,
                selection: state.get( 'selection' ),
                model: state,
                scrollElement: document,
                autoSelect: true,
                suggestedWidth: options.suggestedWidth,
                suggestedHeight: options.suggestedHeight,
            } );

            region.view = view;
        },
    } );

    wp.media.view.MediaFrame.Select = FolderMediaFrameSelect;

    // Override the FooGallery Select frame (wp.foogallery.media.Select) so folders show in FooGallery-owned modals that set frame: "select".
    if ( wp.foogallery && wp.foogallery.media && wp.foogallery.media.Select ) {
        var OriginalFooGallerySelect = wp.foogallery.media.Select;

        var FolderFooGallerySelect = OriginalFooGallerySelect.extend( {
            browseContent: function( region ) {
                var state    = this.state();
                var options  = this.options;
                var library  = state.get( 'library' );

                region.view = new FolderAttachmentsBrowser( {
                    controller: this,
                    collection: library,
                    selection: state.get( 'selection' ),
                    model: state,
                    scrollElement: document,
                    autoSelect: true,
                    suggestedWidth: options.suggestedWidth,
                    suggestedHeight: options.suggestedHeight,
                } );
            },
        } );

        wp.foogallery.media.Select = FolderFooGallerySelect;
    }

}( jQuery, window.wp ));
