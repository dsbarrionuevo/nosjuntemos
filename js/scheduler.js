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
        thead += "<th>Horario</th>";
        for (var i = 0; i < days.length; i++) {
            thead += "<th>" + days[i] + "</th>";
        }
        thead += "</tr></thead>";
        tableHtml += thead;

        var tbody = "<tbody>";
        finishTime++;
        for (var i = initTime; i <= finishTime; i++) {
            var time = i + ":00";
            var tr = "<tr>";
            if (i == finishTime) {
                tr += "<td class='unused'></td>";
            } else if (i == initTime) {
                tr += "<td class='unused'><label>" + time + "</label></td>";
            }
            else {
                tr += "<td><label>" + time + "</label></td>";
            }
            for (var j = 0; j < days.length; j++) {
                if (i == initTime || i == finishTime) {
                    tr += "<td class='unused'></td>";
                } else {
                    tr += "<td class='timecell'><span></span></td>";
                }
            }
            tr += "</tr>";
            tbody += tr;
        }
        tbody += "</tbody>";

        tableHtml += tbody;
        table.html(tableHtml);
        table.addClass("scheduler");

        if (params.addClickEvent != undefined && params.addClickEvent == true) {
            $(document).on("click", ".timecell", function () {
                $(this).toggleClass("marked");
            });
        }
    };

    me.getMarks = function () {
        var results = [];//days
        for (var i = 0; i < days.length; i++) {
            var day = {};
            day.id = i;
            day.name = days[i];
            day.intervals = [];
            results.push(day);
        }
        $(".marked").each(function () {
            var $this = $(this);
            var dayIndex = $this.index();
            var hourIndex = $this.parent().index() + 1;

            var initHour = parseInt(initTime) + (hourIndex - 1) - 1;
            var endHour = initHour + 1;//1 is the interval

            var interval = {};
            interval.initHour = initHour;
            interval.endHour = endHour;
            results[dayIndex - 1].intervals.push(interval);
        });

        //group close intervals
//        for (var i = 0; i < results.length; i++) {
//            var day = results[i];
//            var newIntervals = [];
//            for (var j = 0; j < day.intervals.length; j++) {
//                
//            }
//        }
        //console.log(results);
        return results;
    };
    
    me.getMarksCount = function(){
        return  $(".marked").length;
    };

    me.getTimecell = function (targetDay, targetInitHour) {
        var dayIndex = -1;
        for (var i = 0; i < days.length; i++) {
            if (days[i] == targetDay) {
                dayIndex = i + 1;
            }
        }
        var hourIndex = -1;
        for (var i = initTime; i < finishTime; i++) {
            if (i == targetInitHour) {
                hourIndex = i - initTime + 1;
            }
        }
        return table.find("tbody tr").eq(hourIndex).find("td").eq(dayIndex);
    };

    return me;

})();