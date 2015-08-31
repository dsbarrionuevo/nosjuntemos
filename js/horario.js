var Scheduler = (function () {

    var me = {};

    var table;
    var days;
    var initTime;
    var finishTime;

    me.create = function (params) {
        table = params.table; //jObject
        days = params.days;
        initTime = params.initTime;//00 - 24, whole hours
        finishTime = params.finishTime;

        var tableHtml = "";
        //thead
        var thead = "<thead><tr>";
        thead += "<th>Time</th>";
        for (var i = 0; i < days.length; i++) {
            thead += "<th>" + days[i] + "</th>";
        }
        thead += "</tr></thead>";
        tableHtml += thead;

        var tbody = "<tbody>";
        for (var i = initTime; i <= finishTime; i++) {
            var time = i + ":00";
            var tr = "<tr>";
            if (i === finishTime) {
                tr = "<td></td>";
            } else {
                tr += "<td><label>" + time + "</label></td>";
            }
            for (var j = 0; j < days.length; j++) {
                tr += "<td class='timecell'></td>";
            }
            tr += "</tr>";
            tbody += tr;
        }
        tbody += "</tbody>";

        tableHtml += tbody;
        table.html(tableHtml);
        
        $(document).on("click", ".timecell", function(){
           $(this).toggleClass("marked");
        });
    };
    
    

    return me;

})();