/*!
 * oyonavigator.js 1.0
 * tested with jQuery 3.4.0
 * http://www.oyoweb.nl
 *
 * © 2021 oYoSoftware
 * MIT License
 *
 * oyonavigator is a tool to page through database records
 */

function oyoNavigator(records, recordsPage, pageRange, baseUrl, pageTarget) {

    var pageNo = 1;
    var active = false;
    var objUrl = "";
    var objTarget;
    var first, previous, next, last;
    var defaultBackgroundColor = "#527FC3";
    var defaultFillColor = "white";
    var objScale = 1;

    if (baseUrl !== undefined) {
        objUrl = baseUrl;
    }
    if (pageTarget !== undefined) {
        objTarget = pageTarget;
    }

    var totalPages = Math.floor((records - 1) / recordsPage) + 1;
    if (totalPages < 1) {
        totalPages = 1;
    }

    var navigator = document.createElement("div");
    $(navigator).attr("class", "oyonavigator");
    $(navigator).css("padding-left", "4px");
    $(navigator).css("white-space", "nowrap");
    $(navigator).css("overflow", "hidden");
    $(navigator).css("text-align", "center");

    createImages();

    Object.defineProperty(navigator, "baseUrl", {
        get: function () {
            return objUrl;
        },
        set: function (value) {
            objUrl = value;
        }
    });

    Object.defineProperty(navigator, "pageTarget", {
        get: function () {
            return objTarget;
        },
        set: function (value) {
            objTarget = value;
        }
    });

    function createImages() {
        var svgNS = 'http://www.w3.org/2000/svg';

        first = document.createElementNS(svgNS, "svg");
        $(first).attr("class", "oyocontent");
        $(first).css("border", "0.5px solid black");
        $(first).css("background-color", defaultBackgroundColor);
        $(first).css("border-radius", "2.5px");
        $(first).css("vertical-align", "middle");
        $(first).css("margin-right", "4px");
        $(first).width(16);
        $(first).height(16);

        var rect = document.createElementNS(svgNS, "rect");
        $(rect).attr("class", "oyofill");
        $(rect).css("fill", defaultFillColor);
        $(rect).attr("x", "3.75");
        $(rect).attr("y", "2.5");
        $(rect).attr("width", "1.5");
        $(rect).attr("height", "10");
        $(first).append(rect);
        var polygon = document.createElementNS(svgNS, "polygon");
        $(polygon).attr("class", "oyofill");
        $(polygon).css("fill", defaultFillColor);
        $(polygon).attr("points", "11.25,2.5 11.25,12.5 4.75,7.5");
        $(first).append(polygon);

        previous = document.createElementNS(svgNS, "svg");
        $(previous).attr("class", "oyocontent");
        $(previous).css("border", "0.5px solid black");
        $(previous).css("background-color", defaultBackgroundColor);
        $(previous).css("border-radius", "2.5px");
        $(previous).css("vertical-align", "middle");
        $(previous).css("margin-right", "4px");
        $(previous).width(16);
        $(previous).height(16);

        var polygon = document.createElementNS(svgNS, "polygon");
        $(polygon).attr("class", "oyofill");
        $(polygon).css("fill", defaultFillColor);
        $(polygon).attr("points", "10,2.5 10,12.5 3.5,7.5");
        $(previous).append(polygon);

        next = document.createElementNS(svgNS, "svg");
        $(next).attr("class", "oyocontent");
        $(next).css("border", "0.5px solid black");
        $(next).css("background-color", defaultBackgroundColor);
        $(next).css("border-radius", "2.5px");
        $(next).css("vertical-align", "middle");
        $(next).css("margin-right", "4px");
        $(next).width(16);
        $(next).height(16);

        var polygon = document.createElementNS(svgNS, "polygon");
        $(polygon).attr("class", "oyofill");
        $(polygon).css("fill", defaultFillColor);
        $(polygon).attr("points", "5,2.5 5,12.5 11.5,7.5");
        $(next).append(polygon);

        last = document.createElementNS(svgNS, "svg");
        $(last).attr("class", "oyocontent");
        $(last).css("border", "0.5px solid black");
        $(last).css("background-color", defaultBackgroundColor);
        $(last).css("border-radius", "2.5px");
        $(last).css("vertical-align", "middle");
        $(last).css("margin-right", "4px");
        $(last).width(16);
        $(last).height(16);

        var rect = document.createElementNS(svgNS, "rect");
        $(rect).attr("class", "oyofill");
        $(rect).css("fill", defaultFillColor);
        $(rect).attr("x", "9.75");
        $(rect).attr("y", "2.5");
        $(rect).attr("width", "1.5");
        $(rect).attr("height", "10");
        $(last).append(rect);
        var polygon = document.createElementNS(svgNS, "polygon");
        $(polygon).attr("class", "oyofill");
        $(polygon).css("fill", defaultFillColor);
        $(polygon).attr("points", "3.75,2.5 3.75,12.5 10.25,7.5");
        $(last).append(polygon);
    }

    navigator.setPage = function (startPage, baseUrl) {
        if (startPage === undefined || startPage === 0 || startPage > totalPages) {
            pageNo = 1;
        } else {
            pageNo = startPage;
        }
        if (baseUrl !== undefined) {
            objUrl = baseUrl;
        }

        var url;
        var pos = objUrl.search("[?]");
        if (pos === -1) {
            url = objUrl + "?pageno=";
        } else {
            url = objUrl + "&pageno=";
        }

        var href;

        $("a", navigator).remove();

        if (pageNo === 1) {
            $(first).css("opacity", 0.5);
            $(navigator).append(first);
        } else {
            var a = document.createElement("a");
            $(a).attr("class", "oyopage");
            if (objUrl) {
                href = url + 1;
                $(a).prop("href", href);
                if (objTarget) {
                    $(a).attr("target", objTarget);
                }
            }
            $(navigator).append(a);
            $(first).css("opacity", 1);
            $(a).append(first);
        }

        if (pageNo === 1) {
            $(previous).css("opacity", 0.5);
            $(navigator).append(previous);
        } else {
            var a = document.createElement("a");
            $(a).attr("class", "oyopage");
            if (objUrl) {
                var prevPageNo = pageNo - 1;
                href = url + prevPageNo;
                $(a).prop("href", href);
                if (objTarget) {
                    $(a).attr("target", objTarget);
                }
            }
            $(navigator).append(a);
            $(previous).css("opacity", 1);
            $(a).append(previous);
        }

        if (pageRange > 0) {
            var firstPage = 0;
            var lastPage = 0;

            switch (true) {
                case (pageRange < totalPages && (pageNo + 0.5 * pageRange) > totalPages) :
                    firstPage = totalPages - pageRange + 1;
                    break;
                case (pageRange < totalPages && (pageNo - 0.5 * pageRange) > 0) :
                    firstPage = pageNo - Math.round(0.5 * pageRange) + 1;
                    break;
                default:
                    firstPage = 1;
            }

            switch (true) {
                case ((pageNo + 0.5 * pageRange) > totalPages || (pageRange + 1) > totalPages) :
                    lastPage = totalPages;
                    break;
                case (firstPage === 1) :
                    lastPage = pageRange;
                    break;
                default:
                    lastPage = pageNo + Math.floor(0.5 * pageRange);
            }
        }

        for (var page = firstPage; page <= lastPage; page++) {
            var a = document.createElement("a");
            $(a).attr("class", "oyopage");
            $(a).css("vertical-align", "middle");
            $(a).css("margin-right", "4px");
            $(a).css("font-weight", "bold");
            if (objUrl) {
                href = url + page;
                $(a).prop("href", href);
                if (objTarget) {
                    $(a).attr("target", objTarget);
                }
            }
            $(navigator).append(a);
            if (page === pageNo) {
                $(a).css("font-size", "20px");
                $(a).css("pointer-events", "none");
                $(a).addClass("oyocurrentpage");
            } else {
                $(a).css("font-size", "16px");
            }
            $(a).html(page);
        }

        if (pageNo === totalPages) {
            $(next).css("opacity", 0.5);
            $(navigator).append(next);
        } else {
            var a = document.createElement("a");
            $(a).attr("class", "oyopage");
            if (objUrl) {
                var nextPageNo = pageNo + 1;
                href = url + nextPageNo;
                $(a).prop("href", href);
                if (objTarget) {
                    $(a).attr("target", objTarget);
                }
            }
            $(navigator).append(a);
            $(next).css("opacity", 1);
            $(a).append(next);
        }

        if (pageNo === totalPages) {
            $(last).css("opacity", 0.5);
            $(navigator).append(last);
        } else {
            var a = document.createElement("a");
            $(a).attr("class", "oyopage");
            if (objUrl) {
                href = url + totalPages;
                $(a).prop("href", href);
                if (objTarget) {
                    $(a).attr("target", objTarget);
                }
            }
            $(navigator).append(a);
            $(last).css("opacity", 1);
            $(a).append(last);
        }

        if (objScale !== 1) {
            scale();
        }

        $(".oyopage", navigator).on("click", function (event) {
            var parameters = event.currentTarget.search.substring(1).split("&");
            var pageParameterIndex = parameters.length - 1;
            pageNo = parseInt(parameters[pageParameterIndex].split("=")[1]);
            navigator.setPage(pageNo, objUrl);
        });

        $(".oyopage", navigator).css("text-decoration", "none");

        $(".oyopage", navigator).on("mouseover", function (event) {
            $(event.target).css("text-decoration", "underline");
        });

        $(".oyopage", navigator).on("mouseout", function (event) {
            $(event.target).css("text-decoration", "none");
        });

        if (startPage && objUrl && objTarget && !active) {
            active = true;
            var frame = document.getElementsByName(objTarget)[0];
            var a;
            if (pageNo === 1) {
                a = $(".oyopage", navigator).eq(pageNo - 1);
            } else {
                a = $(".oyopage", navigator).eq(pageNo + 1);
            }
            frame.contentDocument.location.href = a.attr("href");
        }

    };

    navigator.changeBackgroundColor = function (color) {
        $(first).css("background-color", color);
        $(previous).css("background-color", color);
        $(next).css("background-color", color);
        $(last).css("background-color", color);
    };

    navigator.changeFillColor = function (color) {
        $(first).find("*").css("fill", color);
        $(previous).find("*").css("fill", color);
        $(next).find("*").css("fill", color);
        $(last).find("*").css("fill", color);
    };

    navigator.resetColors = function () {
        $(first).css("background-color", defaultBackgroundColor);
        $(previous).css("background-color", defaultBackgroundColor);
        $(next).css("background-color", defaultBackgroundColor);
        $(last).css("background-color", defaultBackgroundColor);
        $(first).find("*").css("fill", defaultFillColor);
        $(previous).find("*").css("fill", defaultFillColor);
        $(next).find("*").css("fill", defaultFillColor);
        $(last).find("*").css("fill", defaultFillColor);
    };

    navigator.scale = function (value) {
        objScale = value;
    };

    function scale() {
        var marginRight = objScale * 4;
        var fontSize = objScale * 16;
        var width = objScale * 15;
        var height = objScale * 15;
        var borderWidth = objScale * 0.5;
        var borderRadius = objScale * 2.5;

        $(".oyopage", navigator).css("margin-right", marginRight + "px");
        $(".oyopage", navigator).css("vertical-align", "middle");
        $(".oyopage", navigator).css("font-size", fontSize + "px");
        $(".oyocurrentpage", navigator).css("font-size", 1.25 * fontSize + "px");

        $(".oyocontent", navigator).css("margin-right", marginRight + "px");
        $(".oyopage", navigator).find(".oyocontent").css("margin-right", "0px");
        $(".oyocontent", navigator).css("border-width", borderWidth + "px");
        $(".oyocontent", navigator).css("border-radius", borderRadius + "px");

        $(".oyocontent", navigator).width(width);
        $(".oyocontent", navigator).height(height);

        var elements = $(".oyocontent", navigator).find("*");
        var svgCSSScale = "scale(" + objScale + ")";
        $(elements).each(function (index, element) {
            $(element).css("transform", svgCSSScale);
        });

    }

    return navigator;

}