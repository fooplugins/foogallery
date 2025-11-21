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
        dropHere: 'Drop to move selected items here',
        assigning: 'Assigning…',
        assignmentSuccess: 'Folder assignment saved.',
        assignmentFailure: 'Could not update folder. Please try again.',
        dragToFolder: 'Drag selected items to a folder to assign it.',
    }, settings.strings || {} );

    var MediaFolders = {
        draggedIds: [],

        setDraggedIds: function( ids ) {
            this.draggedIds = ids;
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

                MediaFolders.setDraggedIds( ids );

                if ( event.originalEvent && event.originalEvent.dataTransfer ) {
                    event.originalEvent.dataTransfer.effectAllowed = 'move';
                    event.originalEvent.dataTransfer.setData( 'text/plain', ids.join( ',' ) );
                }
            } );
        },
    };

    var FolderTreeView = wp.media.View.extend( {
        className: 'foogallery-media-folders-wrapper',

        events: {
            'click .foogallery-folder-label': 'onFolderClick',
            'keydown .foogallery-folder-label': 'onFolderKeydown',
            'dragover li.foogallery-folder-node': 'onDragOver',
            'dragleave li.foogallery-folder-node': 'onDragLeave',
            'drop li.foogallery-folder-node': 'onDrop',
        },

        initialize: function( options ) {
            this.controller = options.controller;
            this.library    = options.library;
            this.selected   = 0;
            this.terms      = settings.terms || [];
            this.termLookup = this.buildTermLookup( this.terms );
        },

        buildTermLookup: function( terms ) {
            var lookup = {};
            terms.forEach( function( term ) {
                lookup[ term.parent ] = lookup[ term.parent ] || [];
                lookup[ term.parent ].push( term );
            } );
            return lookup;
        },

        render: function() {
            this.$el.empty();
            this.$el.append( '<div class="foogallery-media-folders-header"><span class="foogallery-folder-title">' + settings.strings.foldersHeading + '</span><p class="description">' + settings.strings.dragToFolder + '</p></div>' );
            this.$el.append( this.renderList( 0, true ) );
            this.$el.append( '<div class="foogallery-media-folders-status" aria-live="polite"></div>' );
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
            }

            ( this.termLookup[ parentId ] || [] ).forEach( function( term ) {
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

            var $label = $( '<a />', {
                'class': 'foogallery-folder-label',
                'href': '#',
                'data-folder-id': term.id,
                'text': term.name,
                'title': isRoot ? settings.strings.allFolders : settings.strings.dropHere,
            } );

            if ( isRoot ) {
                $label.addClass( 'is-root' );
            }

            $node.append( $label );
            return $node;
        },

        onFolderClick: function( event ) {
            event.preventDefault();
            var folderId = parseInt( $( event.currentTarget ).data( 'folderId' ), 10 );
            this.setFolder( isNaN( folderId ) ? 0 : folderId );
        },

        onFolderKeydown: function( event ) {
            if ( event.key === 'Enter' || event.key === ' ' ) {
                this.onFolderClick( event );
            }
        },

        onDragOver: function( event ) {
            event.preventDefault();
            $( event.currentTarget ).addClass( 'is-drag-over' );
            if ( event.originalEvent && event.originalEvent.dataTransfer ) {
                event.originalEvent.dataTransfer.dropEffect = 'move';
            }
        },

        onDragLeave: function( event ) {
            $( event.currentTarget ).removeClass( 'is-drag-over' );
        },

        onDrop: function( event ) {
            event.preventDefault();
            var $target  = $( event.currentTarget );
            var folderId = parseInt( $target.data( 'folderId' ), 10 );

            $target.removeClass( 'is-drag-over' );

            if ( ! settings.canAssign || ! folderId ) {
                return;
            }

            var selection = this.controller && this.controller.state ? this.controller.state().get( 'selection' ) : false;
            var ids       = MediaFolders.getDraggedIds( selection );

            if ( ! ids.length && event.originalEvent && event.originalEvent.dataTransfer ) {
                var data = event.originalEvent.dataTransfer.getData( 'text/plain' );
                ids      = data ? data.split( ',' ).map( function( id ) { return parseInt( id, 10 ); } ) : [];
                ids      = ids.filter( function( id ) { return !! id; } );
            }

            if ( ! ids.length ) {
                return;
            }

            this.assignToFolder( ids, folderId );
        },

        assignToFolder: function( ids, folderId ) {
            var $status = this.$el.find( '.foogallery-media-folders-status' );
            $status.text( settings.strings.assigning );

            wp.ajax.post( 'foogallery_assign_media_categories', {
                term_id: folderId,
                attachment_ids: ids,
                nonce: settings.nonce,
            } ).done( function() {
                $status.text( settings.strings.assignmentSuccess );
                this.library.props.set( 'foogallery_folder', folderId );
                this.library.more().then( function() {
                    this.controller.trigger( 'selection:action:attachment:update' );
                }.bind( this ) );
            }.bind( this ) ).fail( function() {
                $status.text( settings.strings.assignmentFailure );
            } );
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
            this.library.props.set( 'foogallery_folder', folderId );
            this.library.more().then( function() {
                this.controller.trigger( 'selection:action:attachment:update' );
            }.bind( this ) );
        },
    } );

    var FolderAttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend( {
        createSidebar: function() {
            wp.media.view.AttachmentsBrowser.prototype.createSidebar.apply( this, arguments );

            var folderTree = new FolderTreeView( {
                controller: this.controller,
                library: this.options.controller.state().get( 'library' ),
            } );

            this.views.add( folderTree );
        },

        toggleServerRendering: function() {
            wp.media.view.AttachmentsBrowser.prototype.toggleServerRendering.apply( this, arguments );
            MediaFolders.ensureAttachmentsDraggable( this );
        },
    } );

    var FolderMediaFrame = wp.media.view.MediaFrame.Post.extend( {
        browseRouter: function( routerView ) {
            routerView.set( { library: { text: wp.media.view.l10n.mediaLibraryTitle } } );
        },

        bindHandlers: function() {
            wp.media.view.MediaFrame.Post.prototype.bindHandlers.apply( this, arguments );
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
                dragInfoText: settings.strings.dropHere,
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
}( jQuery, window.wp ));
