/**
 * Created by Kit Ushakov on 10/20/2015.
 */

var Tasks = Backbone.Collection.extend({
    model: Task,

    url: '/frontend/web/index.php?r=task/get',

    /*parse: function(response) {
     debugger;
     //new Task(response[1])
     return response.results;
     }*/

    /*parse: function(response) {
     debugger;
     return _(response.rows).map(function(row) { return row.value ;});
     }*/
    3434
});