(function($, view, model){

    var EXCLUDE_SELECTOR = '.foogallery-attachment-ignore-change';

    // hold reference to the original prototype so we can call it's methods
    var original = view.AttachmentCompat.prototype;
    // replace the wp.media.view.AttachmentCompat with our own which allows us to filter fields from the auto-save
    view.AttachmentCompat = view.AttachmentCompat.extend({
        /**
         * @summary Called when the view is ready so we can bind events etc.
         * @description We override the original here so we can hook into the controllers "close" event. The controller
         * is the wp.media.view.MediaFrame.Select so in essence this is hooking into the modal close event.
         */
        ready: function(){
            this.listenTo(this.controller, "close", this.saveAll);
        },
        /**
         * @summary Removes the view and its' `el` from the DOM and unbinds any events.
         * @description We override the original here so we can force a save of all fields just prior to the view being
         * removed. This also offers us the chance to unbind from the controllers' "close" event.
         * @returns {*}
         */
        remove: function(){
            this.saveAll();
            this.stopListening(this.controller, "close", this.saveAll);
            return original.remove.apply(this, arguments);
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
                console.log("excluded-field:", event.target.name);
                return;
            }
            original.save.apply(this, arguments);
        }
    });

    // if you want to sanitize the data being sent back to the server we can override the original
    // wp.media.model.Attachment#saveCompat function using the below.
    var saveCompat = model.Attachment.prototype.saveCompat;
    model.Attachment.prototype.saveCompat = function(data){
        console.log("saveCompat", data);
        return saveCompat.apply(this, arguments);
    };
})(
    jQuery,
    wp.media.view,
    wp.media.model
);