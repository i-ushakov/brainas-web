/**
 * Created by Kit Ushakov on 4/8/2016.
 */


Utils = {
    convertDateToMySQLFormat: function(date) {
        var result = date.getFullYear() + "-"
        + (date.getMonth() < 10 ? "0" + date.getMonth() : date.getMonth()) + "-" +
        (date.getDate() < 10 ?  "0" + date.getDate() : date.getDate()) + " " +
        date.toLocaleTimeString();

        return result;
    }
}