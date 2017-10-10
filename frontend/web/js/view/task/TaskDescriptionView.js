/**
 * Created by kit on 9/7/2017.
 */

var app = app || {};

TaskDescriptionView = Backbone.View.extend({

    events: {
        'click .descriptionCont': 'editDescription',
        'keyup textarea' : 'changeDescriptionHandler',
        'click .task-description-edit .cancel-edit-icon': 'cancelEditDescription',
    },

    template: _.template( $('#task-card-description-template').html()),

    initialize: function (options) {

        this.createMode = options.createMode;

        this.render();

        /*_.bindAll(this,
        );*/
    },

    render: function () {
        var params = {
            description: this.model.get('description'),
        };
        this.$el.html(this.template(params));

        // view elements
        this.descCont = this.$el.find('.descriptionCont');
        this.descriptionEditView = this.$el.find('.task-description-edit');
        this.messageTextArea = this.$el.find('textarea');

        this.$el.attr('id','descriptionCont_' + this.model.get('id'));
    },

    cancelEditDescription: function() {
        this.descCont.toggle();
        this.descriptionEditView.toggle();
    },

    editDescription: function() {
        this.descCont.toggle();
        this.descriptionEditView.toggle();
    },

    changeDescriptionHandler: function() {
        this.messageTextArea.css('background-color', '');
        this.model.set('description', this.messageTextArea.val());
    },

    destroy: function () {
        this.undelegateEvents();
        this.$el.removeData().unbind();
        this.remove();
        Backbone.View.prototype.remove.call(this);
    }
});