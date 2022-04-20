/*!
 * oyotableheader.js 1.0
 * tested with jQuery 3.4.0
 * http://www.oyoweb.nl
 *
 * © 2022 oYoSoftware
 * MIT License
 *
 * oyotableheader is a tool to give a table a fixed header
 */

function oyoTableHeader(refTable, height) {

    var defaultBackgroundColor = "#527FC3";
    var defaultTextColor = "white";
    var backgroundColor = defaultBackgroundColor;
    var textColor = defaultTextColor;

    var intBorderType = "cell";
    var intBorder = "0.5px solid black";

    var table = document.createElement("table");
    $(table).attr("class", "oyotable");
    $(table).css("background-color", defaultBackgroundColor);
    $(table).css("color", defaultTextColor);
    if (height !== undefined) {
        $(table).height(height);
        $(table).css("max-height", height);
    }
    var thead = document.createElement("thead");
    $(thead).attr("class", "oyotableheader");
    $(table).append(thead);

    var observer = new ResizeObserver(function () {
        resizeTableHeader();
    });
    observer.observe($(refTable)[0]);

    resizeTableHeader = function () {
        $(table).outerWidth($(refTable).outerWidth());
        var clone = $("thead", refTable).clone();
        $(clone).attr("class", "oyotableheader");

        switch (true) {
            case intBorderType === "table":
                $(table).css("border", intBorder);
                $("tr", clone).css("border-width", "0px");
                $("th", clone).css("border-width", "0px");
                break;
            case intBorderType === "cell":
                $(table).css("border-width", "0px");
                $("tr", clone).css("border-width", "0px");
                $("th", clone).css("border", intBorder);
                break;
            case intBorderType === "none":
                $(table).css("border-width", "0px");
                $("tr", clone).css("border-width", "0px");
                $("th", clone).css("border-width", "0px");
                break;
        }

        $(clone).find("tr").each(function (index, element) {
            $(element).attr("class", "oyotableheaderrow");
        });
        $(clone).find("th").each(function (index, element) {
            var cell = $("th", refTable).eq(index);
            var width = parseFloat($(cell).outerWidth());
            $(element).outerWidth(width);
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