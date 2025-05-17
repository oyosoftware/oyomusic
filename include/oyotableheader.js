/*!
 * oyotableheader.js 2.0
 * tested with jQuery 3.4.0
 * http://www.oyoweb.nl
 *
 * © 2025 oYoSoftware
 * MIT License
 *
 * oyotableheader is a tool to give a table a fixed header
 */

function oyoTableHeader(refTable) {

    var defaultBackgroundColor = "#527FC3";
    var defaultTextColor = "white";
    var backgroundColor = defaultBackgroundColor;
    var textColor = defaultTextColor;
    var minHeaderTop = 999;

    var tableHeader = document.createElement("table");
    $(tableHeader).attr("class", "oyotableheader");
    $(tableHeader).css("background-color", defaultBackgroundColor);
    $(tableHeader).css("background-clip", "content-box");
    $(tableHeader).css("color", defaultTextColor);
    $(tableHeader).css("position", "fixed");
    $(tableHeader).css("z-index", 999);

    var tableHeaderHead = document.createElement("thead");
    $(tableHeaderHead).attr("class", "oyotableheaderhead");
    $(tableHeader).append(tableHeaderHead);

    $(tableHeader).insertBefore(refTable);

    var observer = new ResizeObserver(function () {
        resizeTableHeader();
    });
    observer.observe($(refTable)[0]);

    resizeTableHeader = function () {
        $(tableHeader).css("margin-left", $(refTable).css("margin-left"));
        $(tableHeader).css("margin-right", $(refTable).css("margin-right"));
        $(tableHeader).css("border-left", $(refTable).css("border-left"));
        $(tableHeader).css("border-right", $(refTable).css("border-right"));
        $(tableHeader).css("padding-left", $(refTable).css("padding-left"));
        $(tableHeader).css("padding-right", $(refTable).css("padding-right"));
        var clone = $("thead", refTable).clone(true);
        $(clone).attr("class", "oyotableheaderhead");
        $(".oyotableheaderhead", tableHeader).replaceWith(clone);
        var topHeight = parseFloat($(refTable).css("border-top-width")) + parseFloat($(refTable).css("padding-top"));
        $(clone).find("tr").each(function (index, element) {
            $(element).attr("class", "oyotableheaderrow");
        });
        $(clone).find("th").each(function (index, element) {
            var cell = $("th", refTable).eq(index);
            $(element).css("box-sizing", $(cell).css("box-sizing"));
            $(element).outerWidth($(cell).outerWidth());
            $(element).css("min-width", $(cell).outerWidth());
            $(element).outerHeight($(cell).outerHeight() + topHeight);
            $(element).css("min-height", $(cell).outerHeight() + topHeight);
        });
        $(window).trigger("scroll");
    };

    $(window).on("resize", function () {
        $(window).trigger("scroll");
    });

    $(window).on("scroll", function () {
        var tableWidth = $(refTable).outerWidth();
        var tableHeight = $(refTable).outerHeight();
        var tableLeft = $(refTable).get(0).getBoundingClientRect().left;
        var tableTop = $(refTable).get(0).getBoundingClientRect().top;
        var tableRight = tableLeft + tableWidth;
        var tableBottom = tableTop + tableHeight;

        $(tableHeader).css("left", tableLeft);

        if (minHeaderTop === 999) {
            var positionTop = $(refTable).position().top;
            $(tableHeader).css("top", positionTop);
        } else {
            if (minHeaderTop < tableTop) {
                $(tableHeader).css("top", tableTop);
            } else {
                $(tableHeader).css("top", minHeaderTop);
            }
        }

        var headerWidth = $(tableHeader).outerWidth();
        var headerHeight = $(tableHeader).outerHeight();
        var headerLeft = $(tableHeader).get(0).getBoundingClientRect().left;
        var headerTop = $(tableHeader).get(0).getBoundingClientRect().top;
        var headerRight = headerLeft + headerWidth;
        var headerBottom = headerTop + headerHeight;

        if (headerTop < tableBottom && $("tbody tr", refTable).length > 0) {
            $(tableHeader).css("visibility", "visible");
        } else {
            $(tableHeader).css("visibility", "hidden");
        }
        var difX = headerLeft - tableLeft;
        var difY = headerTop - tableTop;
        var x1 = difX;
        var y1 = difY;
        var x2 = tableWidth + 1;
        var y2 = tableHeight + 1;
        var p1 = x1 + "px " + y1 + "px, ";
        var p2 = x2 + "px " + y1 + "px, ";
        var p3 = x2 + "px " + y2 + "px, ";
        var p4 = x1 + "px " + y2 + "px";
        var clipRectTable = "polygon(" + p1 + p2 + p3 + p4 + ")";
        $(refTable).css("clip-path", clipRectTable);
    });

    tableHeader.changeBackgroundColor = function (color) {
        if (color === textColor) {
            if (textColor === "white") {
                color = "black";
            } else {
                color = "white";
            }
        }
        backgroundColor = color;
        $(tableHeader).css("background-color", backgroundColor);
    };

    tableHeader.changeTextColor = function (color) {
        if (color === backgroundColor) {
            if (backgroundColor === "white") {
                color = "black";
            } else {
                color = "white";
            }
        }
        textColor = color;
        $(tableHeader).css("color", textColor);
    };

    tableHeader.resetColors = function () {
        $(tableHeader).css("background-color", defaultBackgroundColor);
        $(tableHeader).css("color", defaultTextColor);
    };

    tableHeader.changeMinTop = function (minY) {
        minHeaderTop = minY;
    };

    return tableHeader;
}