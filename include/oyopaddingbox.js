/*!
 * oyopaddingbox.js 1.0
 * tested with jQuery 3.4.0
 * http://www.oyoweb.nl
 *
 * © 2024 oYoSoftware
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
    var position = $(refObject).css("position");
    $(paddingBox).css("position", "fixed");

    var paddingBoxBorder = document.createElement("div");
    $(paddingBoxBorder).width("100%");
    $(paddingBoxBorder).height("100%");
    $(paddingBoxBorder).css("border", boxBorderBorder);
    $(paddingBox).append(paddingBoxBorder);

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

    var fixedOffsetLeft = $(refObject).offset().left;
    var fixedOffsetTop = $(refObject).offset().top;

    function resizePaddingBox() {
        $(paddingBox).css("display", "block");

        var topBoxSpace = parseFloat($(paddingBox).css("border-top-width")) + parseFloat($(paddingBox).css("padding-top"));
        var leftBoxSpace = parseFloat($(paddingBox).css("border-left-width")) + parseFloat($(paddingBox).css("padding-left"));
        var rightBoxSpace = parseFloat($(paddingBox).css("border-right-width")) + parseFloat($(paddingBox).css("padding-right"));
        var bottomBoxSpace = parseFloat($(paddingBox).css("border-bottom-width")) + parseFloat($(paddingBox).css("padding-bottom"));

        var topBoxBorderSpace = parseFloat($(paddingBoxBorder).css("border-top-width")) + parseFloat($(paddingBoxBorder).css("padding-top"));
        var leftBoxBorderSpace = parseFloat($(paddingBoxBorder).css("border-left-width")) + parseFloat($(paddingBoxBorder).css("padding-left"));
        var rightBoxBorderSpace = parseFloat($(paddingBoxBorder).css("border-right-width")) + parseFloat($(paddingBoxBorder).css("padding-right"));
        var bottomBoxBorderSpace = parseFloat($(paddingBoxBorder).css("border-bottom-width")) + parseFloat($(paddingBoxBorder).css("padding-bottom"));

        var topRefSpace = parseFloat($(refObject).css("border-top-width")) + parseFloat($(refObject).css("padding-top"));
        var leftRefSpace = parseFloat($(refObject).css("border-left-width")) + parseFloat($(refObject).css("padding-left"));
        var rightRefSpace = parseFloat($(refObject).css("border-right-width")) + parseFloat($(refObject).css("padding-right"));
        var bottomRefSpace = parseFloat($(refObject).css("border-bottom-width")) + parseFloat($(refObject).css("padding-bottom"));

        // left, right and width
        var offsetLeft = $(refObject).offset().left;
        var scrollLeft = document.scrollingElement.scrollLeft;
        var windowWidth = $(window).width();
        var minLeft = (offsetLeft % windowWidth) + leftRefSpace - leftBoxSpace - leftBoxBorderSpace;
        var refWidth = $(refObject).width();
        var maxRight = windowWidth - rightRefSpace + rightBoxSpace + rightBoxBorderSpace;
        //var maxRight = windowWidth - rightRefSpace;

        if (position === "fixed") {
            var left = fixedOffsetLeft + leftRefSpace - leftBoxSpace - leftBoxBorderSpace;
        } else {
            var left = offsetLeft + leftRefSpace - leftBoxSpace - leftBoxBorderSpace - scrollLeft;
            if (left < minLeft) {
                left = minLeft;
            }
        }

        $(paddingBox).css("left", left);

        if (position === "fixed") {
            var right = fixedOffsetLeft + leftRefSpace + refWidth + rightBoxSpace + rightBoxBorderSpace;
        } else {
            var right = offsetLeft + leftRefSpace + refWidth + rightBoxSpace + rightBoxBorderSpace;
        }

        if (right > maxRight) {
            right = maxRight;
        }

        width = right - left;
        $(paddingBox).outerWidth(width);

        // top, bottom and height
        var offsetTop = $(refObject).offset().top;
        var scrollTop = document.scrollingElement.scrollTop;
        var windowHeight = $(window).height();
        var minTop = (offsetTop % windowHeight) + topRefSpace - topBoxSpace - topBoxBorderSpace;
        var refHeight = $(refObject).height();
        var maxBottom = windowHeight - bottomRefSpace + bottomBoxSpace + bottomBoxBorderSpace;
        //var maxBottom = windowHeight - bottomRefSpace;

        if (position === "fixed") {
            var top = fixedOffsetTop + topRefSpace - topBoxSpace - topBoxBorderSpace;
        } else {
            var top = offsetTop + topRefSpace - topBoxSpace - topBoxBorderSpace - scrollTop;
            if (top < minTop) {
                top = minTop;
            }
        }

        $(paddingBox).css("top", top);

        if (position === "fixed") {
            var bottom = fixedOffsetTop + topRefSpace + refHeight + bottomBoxSpace + bottomBoxBorderSpace;
        } else {
            var bottom = offsetTop + topRefSpace + refHeight + bottomBoxSpace + bottomBoxBorderSpace;
        }

        if (bottom > maxBottom) {
            bottom = maxBottom;
        }

        height = bottom - top;
        $(paddingBox).outerHeight(height);

        // Make clipping box
        $(refObject).css("clip-path", "none");

        var width = $(paddingBoxBorder).width();
        var height = $(paddingBoxBorder).height();
        var offsetLeftBox = $(paddingBox).offset().left;
        var offsetTopBox = $(paddingBox).offset().top;

        var x1 = offsetLeftBox + leftBoxSpace + leftBoxBorderSpace - offsetLeft;
        if (x1 < 0) {
            x1 = 0;
        }
        var y1 = offsetTopBox + topBoxSpace + topBoxBorderSpace - offsetTop;
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
        fixedOffsetLeft = $(refObject).offset().left;
        fixedOffsetTop = $(refObject).offset().top;
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