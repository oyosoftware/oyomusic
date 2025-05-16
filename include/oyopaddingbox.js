/*!
 * oyopaddingbox.js 1.0
 * tested with jQuery 3.4.0
 * http://www.oyoweb.nl
 *
 * © 2025 oYoSoftware
 * MIT License
 *
 * oyopaddingbox is a tool to clip an object on it's padding box
 */

function oyoPaddingBox(refObject) {

    var boxBorder = "3px double black";
    var scrollBar = {};

    var paddingBox = document.createElement("div");
    $(paddingBox).attr("class", "oyopaddingbox");
    $(paddingBox).css("margin", "0px");
    $(paddingBox).css("border", boxBorder);
    $(paddingBox).css("padding", "0px");
    $(paddingBox).css("position", "fixed");

    var position = $(refObject).css("position");
    $(paddingBox).insertBefore(refObject);

    var paddingBoxScroll = document.createElement("div");
    $(paddingBoxScroll).css("overflow", "auto");
    $(paddingBoxScroll).width("100px");
    $(paddingBoxScroll).height("100px");
    $("body").append(paddingBoxScroll);

    var paddingBoxScrollElement = document.createElement("div");
    $(paddingBoxScrollElement).width("200px");
    $(paddingBoxScrollElement).height("200px");
    $(paddingBoxScroll).append(paddingBoxScrollElement);

    var scrollBarWidth = paddingBoxScroll.offsetWidth - paddingBoxScroll.clientWidth;
    var scrollBarHeight = paddingBoxScroll.offsetHeight - paddingBoxScroll.clientHeight;
    $(paddingBoxScroll).remove();

    Object.defineProperty(scrollBar, "width", {
        get: function () {
            var scrollingElement = document.scrollingElement;
            var vertical = (scrollingElement.scrollHeight > scrollingElement.clientHeight);
            return vertical ? scrollBarWidth : 0;
        }
    });

    Object.defineProperty(scrollBar, "height", {
        get: function () {
            var scrollingElement = document.scrollingElement;
            var horizontal = (scrollingElement.scrollWidth > scrollingElement.clientWidth);
            return horizontal ? scrollBarHeight : 0;
        }
    });

    $(window).on("resize", function () {
        resizePaddingBox();
    });

    $(window).on("scroll", function () {
        resizePaddingBox();
    });

    var observer = new ResizeObserver(function () {
        resizePaddingBox();
    });
    observer.observe($(refObject)[0]);

    function resizePaddingBox() {
        var leftSpace = parseFloat($(paddingBox).css("border-left-width"));
        var rightSpace = parseFloat($(paddingBox).css("border-right-width"));
        var topSpace = parseFloat($(paddingBox).css("border-top-width"));
        var bottomSpace = parseFloat($(paddingBox).css("border-bottom-width"));

        var leftRefSpace = parseFloat($(refObject).css("border-left-width")) + parseFloat($(refObject).css("padding-left"));
        var rightRefSpace = parseFloat($(refObject).css("border-right-width")) + parseFloat($(refObject).css("padding-right"));
        var topRefSpace = parseFloat($(refObject).css("border-top-width")) + parseFloat($(refObject).css("padding-top"));
        var bottomRefSpace = parseFloat($(refObject).css("border-bottom-width")) + parseFloat($(refObject).css("padding-bottom"));

        // left, right and width
        $(paddingBox).css("left", 0);
        var boundingLeft = $(refObject).get(0).getBoundingClientRect().left;
        var scrollLeft = document.scrollingElement.scrollLeft;
        var left = boundingLeft + scrollLeft + leftRefSpace - leftSpace;
        $(paddingBox).css("left", left);
        var refWidth = $(refObject).outerWidth() - leftRefSpace - rightRefSpace;
        var right = boundingLeft + leftRefSpace + refWidth + rightSpace;

        var windowWidth = window.innerWidth;
        var maxRight = windowWidth - rightRefSpace + rightSpace - scrollBar.width;
        if (right > maxRight) {
            right = maxRight;
        }

        width = right - left;
        $(paddingBox).outerWidth(width);

        // top, bottom and height
        var boundingTop = $(refObject).get(0).getBoundingClientRect().top;
        var scrollTop = document.scrollingElement.scrollTop;
        if (position === "fixed") {
            scrollTop = 0;
        }
        var top = boundingTop + scrollTop + topRefSpace - topSpace;
        $(paddingBox).css("top", top);
        var refHeight = $(refObject).outerHeight() - topRefSpace - bottomRefSpace;
        var bottom = boundingTop + topRefSpace + refHeight + bottomSpace;

        var windowHeight = window.innerHeight;
        var maxBottom = windowHeight - bottomRefSpace + bottomSpace - scrollBar.height;
        if (bottom > maxBottom) {
            bottom = maxBottom;
        }

        if ($(refObject).css("display") === "none" || $(refObject).css("visibility") === "hidden") {
            height = 0;
        } else {
            height = bottom - top;
        }
        $(paddingBox).outerHeight(height);

        // Make clipping box
        var width = $(paddingBox).width();
        var height = $(paddingBox).height();

        var x1 = left - boundingLeft + leftSpace;
        var y1 = top - boundingTop + topSpace;

        var x2 = x1 + width - 0.39;
        var y2 = y1 + height - 0.2;

        if (x1 > x2 || y1 > y2) {
            $(paddingBox).css("display", "none");
        } else {
            $(paddingBox).css("display", "block");
        }

        var p1 = x1 + "px " + y1 + "px, ";
        var p2 = x2 + "px " + y1 + "px, ";
        var p3 = x2 + "px " + y2 + "px, ";
        var p4 = x1 + "px " + y2 + "px";
        var clipRect = "polygon(" + p1 + p2 + p3 + p4 + ")";

        $(refObject).css("clip-path", clipRect);
    }

    paddingBox.resize = function () {
        resizePaddingBox();
    };

    paddingBox.changeBorder = function (border = boxBorder) {
        boxBorder = border;
        $(paddingBox).css("border", boxBorder);
    };

    return paddingBox;
}