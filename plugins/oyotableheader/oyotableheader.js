/*!
 * oyotableheader.js 1.0
 * tested with jQuery 3.4.0
 * http://www.oyoweb.nl
 *
 * © 2022 oYoSoftware
 * MIT License
 *
 * oyotableheader is a tool to define a fixed table header
 */

function oyoTableHeader(refTable, height) {

    var defaultBackgroundColor = "#527FC3";
    var defaultTextColor = "white";
    var backgroundColor = defaultBackgroundColor;
    var textColor = defaultTextColor;

    var intBorderType = "cell";
    var intBorder = "1px solid black";
    var intHeaderBorder = $("tr", refTable).css("border");
    var intCellBorder = $("th", refTable).css("border");

    var table = document.createElement("table");
    $(table).attr("class", "oyotable");
    $(table).css("background-color", defaultBackgroundColor);
    $(table).css("color", defaultTextColor);
    if (height !== undefined) {
        $(table).height(height);
        $(table).css("max-height", height);
    }
    var thead = document.createElement("thead");
    $(table).append(thead);
    var tr = document.createElement("tr");
    $(tr).attr("class", "oyotableheader");
    $(thead).append(tr);

    var observer = new ResizeObserver(function () {
        resizeTableHeader();
    });
    observer.observe($(refTable)[0]);

    resizeTableHeader = function () {
        $(table).width($(refTable).width());
        var clone = $("thead tr", refTable).clone();
        $(clone).attr("class", "oyotableheader");
        switch (true) {
            case intBorderType === "header":
                $(clone).css("border", intBorder);
                $("th", clone).css("border", "0px");
                break;
            case intBorderType === "cell":
                $(clone).css("border", "0px");
                $("th", clone).css("border", intBorder);
                break;
            case intBorderType === "original":
                $(clone).css("border", intHeaderBorder);
                $("th", clone).css("border", intCellBorder);
                break;
            case intBorderType === "none":
                $(clone).css("border", "0px");
                $("th", clone).css("border", "0px");
                break;
        }
        $(clone).children().each(function (index, element) {
            var cell = $("tr", refTable).children().eq(index);
            var width = parseFloat($(cell).css("width"));
            $(element).width(width);
        });
        $(".oyotableheader", table).replaceWith(clone);
    };

    table.changeBorder = function (borderType = "cell", border = intBorder) {
        intBorderType = borderType;
        intBorder = border;
    };

    table.changeBackgroundColor = function (color) {
        if (color === textColor) {
            if (textColor === "white") {
                color = "black";
            } else {
                color = "white";
            }
        }
        backgroundColor = color;
        $(table).css("background-color", backgroundColor);
    };

    table.changeTextColor = function (color) {
        if (color === backgroundColor) {
            if (backgroundColor === "white") {
                color = "black";
            } else {
                color = "white";
            }
        }
        textColor = color;
        $(table).css("color", textColor);
    };

    table.resetColors = function () {
        $(table).css("background-color", defaultBackgroundColor);
        $(table).css("color", defaultTextColor);
    };

    return table;
}