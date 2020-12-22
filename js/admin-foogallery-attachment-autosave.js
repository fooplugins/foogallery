(function($, view){

    var EXCLUDE_SELECTOR = '.foogallery-attachment-ignore-change',
        SELECTIZE_SELECTOR = '.foogallery-attachment-selectize';

    // hold reference to the original prototype so we can call it's methods
    var original = view.AttachmentCompat.prototype;
    // replace the wp.media.view.AttachmentCompat with our own which allows us to filter fields from the auto-save
    view.AttachmentCompat = view.AttachmentCompat.extend({
        render : function() {
            var result = original.render.apply( this, arguments );

            if ( !!result ) {
                //we have a valid result and should run our code
                var $selectizeInputs = result.$el.find(SELECTIZE_SELECTOR);
                $selectizeInputs.each( function() {
                    FOOGALLERY_SELECTIZE( this );
                } );
            }
        },

        /**
         * @summary Called when the view is ready so we can bind events etc.
         * @description We override the original here so we can hook into the controllers "close" event. The controller
         * is the wp.media.view.MediaFrame.Select so in essence this is hooking into the modal close event.
         */
        initialize: function(){
            this.dirty = false;
            this.controller.on("close", this.saveAll.bind(this));
            original.initialize.apply(this, arguments);
        },
        /**
         * @summary Disposes of the current view. This method is called prior to removing the view.
         * @description We override the dispose method so we can check if one of the excluded fields has been changed and the
         * view needs to be saved.
         * @returns {wp.media.view.AttachmentCompat}
         */
        dispose: function(){
            if (this.dirty) this.saveAll();
            this.dirty = false;
            return original.dispose.apply(this, arguments);
        },
        /**
         * @summary Forces all fields to be saved regardless of the exclude selector.
         * @description This works as when we call `.save()` with no parameters there is no event object to use as a filter.
         * You can look at this method as the one that is called whenever any excluded fields actually must be saved. At
         * present this is called when the media modal is closed, or when the attachment is removed. The attachment is removed
         * when switching between the Upload Files and Media Library tabs or when changing the selected attachment in the
         * Media Library tab.
         */
        saveAll: function(){
            this.save();
        },
        /**
         * @summary Saves the view.
         * @param {Event} [event] - The event object that triggered the save.
         * @description We override the original here so we can apply our filter which aborts saving if the `target`
         * of the `event` matches the EXCLUDE selector.
         */
        save: function(event){
            if (event && event.target && $(event.target).is(EXCLUDE_SELECTOR)){
                // console.log("excluded-field:", event.target.name);
                this.dirty = true;
                return;
            }
            original.save.apply(this, arguments);
        }
    });
})(
    jQuery,
    wp.media.view
);