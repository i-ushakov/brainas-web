/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var Tasks = Backbone.Collection.extend({
    model: Task,

    url: '/task/get',

    initialize: function() {
        var self = this;
        this.timer = setInterval(function() {
            self.fetch();
        }, 1500000);

        _.bindAll(this, 'parse');
    },

    close: function() {
        clearInterval(this.timer);
    },

    fetch: function(options) {
        if (options == undefined) {
            var options = {};
        }
        var tasksControlBoard = app.mainPanel.get("tasksControlBoard");
        var statusesFilter = tasksControlBoard.get("statusesFilter");
        var typeOfSort = app.mainPanel.getTypeOfSort();
        options.data = {
            statusesFilter : statusesFilter,
            typeOfSort : typeOfSort
        };
        return Backbone.Collection.prototype.fetch.call(this, options);
    },

    parse: function(response, options) {
        var collection = this;
        if (response.status == "FAILED") {
            return this.models;
        }
        var tasks = [];
        _.each(response, function(task) {
            collection.get(task.id);
            var taskModel;
            var currentTaskModel = collection.get(task.id);
            if (currentTaskModel && currentTaskModel.get("preventUpdateFromServer")) {
                taskModel = currentTaskModel;
            } else {
                taskModel = new Task(task);
            }

            tasks.push(taskModel);
        });
        this.set(tasks);
        return this.models;
    },

    comparator: function(task) {
        return -task.id;
    },

    copmoratorTimeAddedNewest: function(task) {
        return task.get('created_utc');
    },

    copmoratorTimeAddedOldest: function(task) {
        return -task.get('created_utc');
    },

    copmoratorLatestChanges: function(task) {
        return task.get('changed_utc');
    },

    copmoratorTaskTitle: function(task) {
        var title = task.get('message');

        return String.fromCharCode.apply(String,
            _.map(title.split(""), function (c) {
                return 0xffff - c.charCodeAt();
            })
        );
    },

    sortByType: function(typeOfSort) {
        if (typeOfSort == "TIME_ADDED_NEWEST") {
            this.comparator = this.copmoratorTimeAddedNewest;
        } else if (typeOfSort == "TIME_ADDED_OLDEST") {
            this.comparator = this.copmoratorTimeAddedOldest;
        } else if (typeOfSort == "LATEST_CHANGES") {
            this.comparator = this.copmoratorLatestChanges;
        } else if (typeOfSort == "TASK_TITLE") {
            this.comparator = this.copmoratorTaskTitle;
        }
        this.sort();
    }

});