/*!
 * oyopaddingbox.js 1.0
 * tested with jQuery 3.4.0
 * http://www.oyoweb.nl
 *
 * © 2022 oYoSoftware
 * MIT License
 *
 * oyopaddingbox is a tool to clip an object on it's padding box
 */

function oyoPaddingBox(refObject) {

    var boxBorder = "0.5px solid black";
    var boxPadding = "1px";
    var boxBorderBorder = "0.5px solid black";

    var paddingBox = document.createElement("div");
    $(paddingBox).attr("class", "oyopaddingbox");
    $(paddingBox).css("box-sizing", "border-box");
    $(paddingBox).css("position", "fixed");
    $(paddingBox).css("border", boxBorder);
    $(paddingBox).css("padding", boxPadding);

    var paddingBoxBorder = document.createElement("div");
    $(paddingBoxBorder).width("100%");
    $(paddingBoxBorder).height("100%");
    $(paddingBoxBorder).css("border", boxBorderBorder);
    $(paddingBox).append(paddingBoxBorder);

    $(window).on("scroll", function () {
        resizePaddingBox();
    });

    var observer = new ResizeObserver(function () {
        resizePaddingBox();
    });
    observer.observe($(refObject)[0]);

    function resizePaddingBox() {
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

        var position = $(refObject).css("position");
        var top, left;
        if (position === "fixed") {
            top = parseFloat($(refObject).css("top")) - topBoxSpace - topBoxBorderSpace + topRefSpace;
        } else {
            top = parseFloat(($(refObject).offset().top - topBoxSpace - topBoxBorderSpace + topRefSpace).toFixed(3));
        }
        $(paddingBox).css("top", top);
        left = parseFloat(($(refObject).offset().left - leftBoxSpace - leftBoxBorderSpace + leftRefSpace).toFixed(3));
        $(paddingBox).css("left", left);

        var padding = parseFloat($(refObject).find("*").css("padding-right"));
        var width = $(refObject).width() + leftBoxSpace + leftBoxBorderSpace + rightBoxSpace + rightBoxBorderSpace - padding;
        $(paddingBox).outerWidth(width);

        if (position === "fixed") {
            var height = document.documentElement.clientHeight - parseFloat($(refObject).css("top"));
        } else {
            var height = document.documentElement.clientHeight - parseFloat($(refObject).offset().top.toFixed(3));
        }
        var padding = parseFloat($(refObject).find("*").css("padding-bottom"));
        if (document.documentElement.scrollHeight <= document.documentElement.clientHeight || $(refObject).outerHeight() <= height) {
            var height = $(refObject).outerHeight() + topBoxSpace + topBoxBorderSpace + bottomBoxSpace + bottomBoxBorderSpace - padding - 0.5;
            $(paddingBox).outerHeight(height);
        } else {
            var height = document.documentElement.clientHeight - parseFloat($(refObject).offset().top.toFixed(3)) + topBoxSpace + topBoxBorderSpace + bottomBoxSpace + bottomBoxBorderSpace - padding - 0.5;
            $(paddingBox).outerHeight(height);
        }

        var width = $(paddingBoxBorder).width();
        var height = $(paddingBoxBorder).height();

        if (position === "fixed") {
            var x1 = $(document.documentElement).scrollLeft() + leftRefSpace;
            var y1 = 0;
            var x2 = $(document.documentElement).scrollLeft() + width + leftRefSpace;
            var y2 = height;
        } else {
            var x1 = $(document.documentElement).scrollLeft() + leftRefSpace;
            var y1 = $(document.documentElement).scrollTop() + 0.5;
            var x2 = $(document.documentElement).scrollLeft() + width + leftRefSpace;
            var y2 = $(document.documentElement).scrollTop() + height;
        }

        var p1 = x1 + "px " + y1 + "px, ";
        var p2 = x2 + "px " + y1 + "px, ";
        var p3 = x2 + "px " + y2 + "px, ";
        var p4 = x1 + "px " + y2 + "px";
        var clipRect = "polygon(" + p1 + p2 + p3 + p4 + ")";

        $(refObject).css("clip-path", clipRect);
    }

    paddingBox.resize = resizePaddingBox;

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

    paddingBox.changePadding = function (padding = boxPadding) {
        boxPadding = padding;
        $(paddingBox).css("padding", boxPadding);
    };

    paddingBox.changeInnerBorder = function (innerBorder = boxBorderBorder) {
        boxBorderBorder = innerBorder;
        $(paddingBoxBorder).css("border", boxBorderBorder);
    };

    paddingBox.changeOuterBorderWidth = function (outerBorderWidth) {
        $(paddingBox).css("border-width", outerBorderWidth);
    };

    paddingBox.changeInnerBorderWidth = function (innerBorderWidth) {
        $(paddingBoxBorder).css("border-width", innerBorderWidth);
    };

    return paddingBox;
}