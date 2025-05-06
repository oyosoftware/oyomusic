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

    var boxBorder = "1px solid black";
    var boxPadding = "1px";
    var boxBorderBorder = "1px solid black";

    var paddingBox = document.createElement("div");
    $(paddingBox).attr("class", "oyopaddingbox");
    $(paddingBox).css("box-sizing", "border-box");
    $(paddingBox).css("border", boxBorder);
    $(paddingBox).css("padding", boxPadding);
    $(paddingBox).css("position", "fixed");

    var paddingBoxBorder = document.createElement("div");
    $(paddingBoxBorder).css("box-sizing", "border-box");
    $(paddingBoxBorder).width("100%");
    $(paddingBoxBorder).height("100%");
    $(paddingBoxBorder).css("border", boxBorderBorder);
    $(paddingBox).append(paddingBoxBorder);

    var position = $(refObject).css("position");
    $(refObject).css("box-sizing", "border-box");
    $(paddingBox).insertBefore(refObject);

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
        $(paddingBox).css("display", "block");

        var leftBoxSpace = parseFloat($(paddingBox).css("border-left-width")) + parseFloat($(paddingBox).css("padding-left"));
        var rightBoxSpace = parseFloat($(paddingBox).css("border-right-width")) + parseFloat($(paddingBox).css("padding-right"));
        var topBoxSpace = parseFloat($(paddingBox).css("border-top-width")) + parseFloat($(paddingBox).css("padding-top"));
        var bottomBoxSpace = parseFloat($(paddingBox).css("border-bottom-width")) + parseFloat($(paddingBox).css("padding-bottom"));

        var leftBoxBorderSpace = parseFloat($(paddingBoxBorder).css("border-left-width")) + parseFloat($(paddingBoxBorder).css("padding-left"));
        var rightBoxBorderSpace = parseFloat($(paddingBoxBorder).css("border-right-width")) + parseFloat($(paddingBoxBorder).css("padding-right"));
        var topBoxBorderSpace = parseFloat($(paddingBoxBorder).css("border-top-width")) + parseFloat($(paddingBoxBorder).css("padding-top"));
        var bottomBoxBorderSpace = parseFloat($(paddingBoxBorder).css("border-bottom-width")) + parseFloat($(paddingBoxBorder).css("padding-bottom"));

        var leftSpace = leftBoxSpace + leftBoxBorderSpace;
        var rightSpace = rightBoxSpace + rightBoxBorderSpace;
        var topSpace = topBoxSpace + topBoxBorderSpace;
        var bottomSpace = bottomBoxSpace + bottomBoxBorderSpace;

        var leftRefSpace = parseFloat($(refObject).css("border-left-width")) + parseFloat($(refObject).css("padding-left"));
        var rightRefSpace = parseFloat($(refObject).css("border-right-width")) + parseFloat($(refObject).css("padding-right"));
        var topRefSpace = parseFloat($(refObject).css("border-top-width")) + parseFloat($(refObject).css("padding-top"));
        var bottomRefSpace = parseFloat($(refObject).css("border-bottom-width")) + parseFloat($(refObject).css("padding-bottom"));
        var topMargin = parseFloat($(refObject).css("margin-top"));

        // left, right and width
        if (position === "fixed") {
            var left = parseInt($(refObject).css("left")) - leftSpace + leftRefSpace;
        } else {
            var left = $(refObject).position().left - leftSpace + leftRefSpace;
        }
        $(paddingBox).css("left", left);
        var windowWidth = $(window).width();
        var refWidth = $(refObject).width();
        var maxRight = windowWidth - rightRefSpace + rightSpace;

        var boundingLeft = $(refObject).get(0).getBoundingClientRect().left;
        var right = boundingLeft + leftRefSpace + refWidth + rightSpace;

        if (right > maxRight) {
            right = maxRight;
        }

        width = right - left;

        $(paddingBox).outerWidth(width);

        if ($(paddingBoxBorder).width() > 0) {
            $(paddingBox).css("display", "block");
        } else {
            $(paddingBox).css("display", "none");
        }

        // top, bottom and height
        if (position === "fixed") {
            var top = parseInt($(refObject).css("top")) - topSpace + topRefSpace + topMargin;
        } else {
            var top = $(refObject).position().top - topSpace + topRefSpace + topMargin;
        }
        $(paddingBox).css("top", top);
        var windowHeight = $(window).height();
        var refHeight = $(refObject).height();
        var maxBottom = windowHeight - bottomRefSpace + bottomSpace;

        var boundingTop = $(refObject).get(0).getBoundingClientRect().top;
        var bottom = boundingTop + topRefSpace + refHeight + bottomSpace;

        if (bottom > maxBottom) {
            bottom = maxBottom;
        }

        if ($(refObject).css("display") === "none" || $(refObject).css("visibility") === "hidden") {
            height = 0;
        } else {
            height = bottom - top;
        }

        $(paddingBox).outerHeight(height);

        if ($(paddingBoxBorder).height() > 0) {
            $(paddingBox).css("display", "block");
        } else {
            $(paddingBox).css("display", "none");
        }

        // Make clipping box
        var offsetLeftBox = $(paddingBox).offset().left;
        var offsetTopBox = $(paddingBox).offset().top;
        var offsetLeft = $(refObject).offset().left;
        var offsetTop = $(refObject).offset().top;
        var width = $(paddingBoxBorder).width();
        var height = $(paddingBoxBorder).height();

        var x1 = offsetLeftBox + leftSpace - offsetLeft;
        if (x1 < 0) {
            x1 = 0;
        }
        var y1 = offsetTopBox + topSpace - offsetTop;
        if (y1 < 0) {
            y1 = 0;
        }
        var x2 = x1 + width;
        if (x2 < 0) {
            x2 = 0;
        }
        var y2 = y1 + height;
        if (y2 < 0) {
            y2 = 0;
        }
        if (x1 === 0 && x2 === 0) {
            $(paddingBox).css("display", "none");
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

    paddingBox.change = function (outerBorder = boxBorder, padding = boxPadding, innerBorder = boxBorderBorder) {
        boxBorder = outerBorder;
        boxPadding = padding;
        boxBorderBorder = innerBorder;
        $(paddingBox).css("border", boxBorder);
        $(paddingBox).css("padding", boxPadding);
        $(paddingBoxBorder).css("border", boxBorderBorder);
    };

    paddingBox.changeOuterBorder = function (outerBorder = boxBorder) {
        boxBorder = outerBorder;
        $(paddingBox).css("border", boxBorder);
    };

    paddingBox.changeOuterBorderWidth = function (outerBorderWidth) {
        $(paddingBox).css("border-width", outerBorderWidth);
    };

    paddingBox.changePadding = function (padding = boxPadding) {
        boxPadding = padding;
        $(paddingBox).css("padding", boxPadding);
    };

    paddingBox.changeInnerBorder = function (innerBorder = boxBorderBorder) {
        boxBorderBorder = innerBorder;
        $(paddingBoxBorder).css("border", boxBorderBorder);
    };

    paddingBox.changeInnerBorderWidth = function (innerBorderWidth) {
        $(paddingBoxBorder).css("border-width", innerBorderWidth);
    };

    return paddingBox;
}