/*!
 * oyoresizer.js 1.0
 * tested with jQuery 3.4.0
 * http://www.oyoweb.nl
 *
 * © 2021 oYoSoftware
 * MIT License
 *
 * oyoresizer is a tool to resize elements, frames included
 */

function oyoresizer(selectors, minX, maxX) {

    var elementDocument = document;

    var selectors = $(selectors);
    var resizeMargin = 0;

    var element;
    $(selectors).each(function (index, target) {
        if (window.frameElement !== target && target.tagName === "IFRAME") {
            var hasOverlay = $(target).parent().find(":first-child").eq(0).hasClass("oyooverlay");
            var className;
            if (!hasOverlay) {
                var htmlContent = $(target).contents().find("html").children();
                var elementCount = $(target).contents().find("body").children().length;
                var wrapper = document.createElement("div");
                var wrapperIndex = $("[class^='oyowrapper']").length + 1;
                $(wrapper).attr("class", "oyowrapper" + wrapperIndex);
                $(wrapper).css("margin", $(target).css("margin"));
                $(target).css("margin", 0);
                $(wrapper).css("position", "relative");
                $(wrapper).innerWidth($(target).outerWidth());
                $(wrapper).innerHeight($(target).outerHeight());
                $(target).wrap(wrapper);
                if (elementCount > 0) {
                    $(target).contents().find("html").html(htmlContent);
                }
                var overlay = document.createElement("div");
                $(overlay).attr("class", "oyooverlay");
                $(overlay).css("position", "absolute");
                resizeMargin = 10;
                $(overlay).outerWidth(resizeMargin);
                $(overlay).innerHeight($(target).outerHeight());
                $(overlay).css("right", 0);
                $(target).before(overlay);
                className = ".oyowrapper" + wrapperIndex;
            } else {
                className = $(target).parent();
            }
            element = $(element).add(className);
        } else {
            element = $(element).add(target);
        }
    });

    $(element).each(function (index, target) {
        if (minX === undefined) {
            $(target).attr("minx", 200);
        } else {
            $(target).attr("minx", minX);
        }
        if (maxX === undefined) {
            $(target).attr("maxx", 500);
        } else {
            $(target).attr("maxx", maxX);
        }
        setWidth(target);
    });

    $(elementDocument).on("mousedown", mouseDown);
    $(elementDocument).on("mousemove", mouseMove);
    $(elementDocument).on("mouseup", mouseUp);

    $(element).each(function (index, target) {
        if (window.frameElement && target.tagName === "IFRAME") {
            $(parent.document).on("mousedown", mouseDown);
            $(parent.document).on("mousemove", mouseMove);
            $(parent.document).on("mouseup", mouseUp);
        }
    });

    $(element).on("mouseenter", mouseEnter);
    $(element).on("mouseleave", mouseLeave);

    function mouseDown(event) {
        var elementArray = $(element).toArray();
        var leadTarget;
        switch (true) {
            case window.frameElement && (event.target.tagName === "HTML" || event.target.tagName === "BODY"):
                leadTarget = window.frameElement;
                break;
            case $(event.target).hasClass("oyooverlay"):
                leadTarget = $(event.target).parent()[0];
                break;
            default:
                leadTarget = event.target;
        }

        if (elementArray.includes(leadTarget)) {
            var pageX, posMax, borders;
            if (window.frameElement && (event.target.tagName === "HTML" || event.target.tagName === "BODY")) {
                pageX = $(event.target).offset().left + parseFloat($(event.target).css("border-left-width")) + event.pageX;
            } else {
                pageX = event.pageX;
            }
            switch (true) {
                case window.frameElement && (event.target.tagName === "HTML" || event.target.tagName === "BODY"):
                    posMax = $(event.target).offset().left + $(event.target).outerWidth();
                    borders = parseFloat($(event.target).css("border-right-width"));
                    break;
                case $(event.target).hasClass("oyooverlay"):
                    posMax = $(event.target).parent().offset().left + $(event.target).parent().outerWidth();
                    borders = parseFloat($(event.target).parent().css("border-right-width")) + parseFloat($(event.target).parent().children().last().css("border-right-width"));
                    break;
                default:
                    posMax = $(event.target).offset().left + $(event.target).outerWidth();
                    borders = parseFloat($(event.target).css("border-right-width"));
            }

            var clientWidth = 0;
            if (window.frameElement && (event.target.tagName === "HTML" || event.target.tagName === "BODY")) {
                clientWidth = document.body.clientWidth;
            } else {
                clientWidth = $(event.target).prop("clientWidth");
            }
            var scrollBarWidth = $(event.target).innerWidth() - clientWidth;
            if (scrollBarWidth < 0) {
                scrollBarWidth = 0;
            }
            var minPosResize = posMax - borders - scrollBarWidth - resizeMargin - 1;
            var maxPosResize = posMax + 1;

            if (pageX >= minPosResize && pageX <= maxPosResize) {
                $(leadTarget).addClass("oyolead");
                $(element).each(function (index, target) {
                    if ($(target).hasClass("oyotarget")) {
                        $(target).addClass("oyoactive");
                    }
                });
            }
        }
        event.stopPropagation();
    }

    function mouseMove(event) {
        var mouseLeftDown = (event.buttons === 1);
        var posChange = 0;
        $(element).each(function (index, target) {
            var pageX, posMax, borders;
            if (window.frameElement && (event.target.tagName === "HTML" || event.target.tagName === "BODY")) {
                pageX = $(target).offset().left + parseFloat($(target).css("border-left-width")) + event.pageX;
            } else {
                pageX = event.pageX;
            }
            switch (true) {
                case window.frameElement && (event.target.tagName === "HTML" || event.target.tagName === "BODY"):
                    posMax = $(target).offset().left + $(target).outerWidth();
                    borders = parseFloat($(target).css("border-right-width"));
                    break;
                case $(event.target).hasClass("oyooverlay"):
                    posMax = $(target).offset().left + $(target).outerWidth();
                    borders = parseFloat($(target).css("border-right-width")) + parseFloat($(target).children().last().css("border-right-width"));
                    break;
                default:
                    posMax = $(target).offset().left + $(target).outerWidth();
                    borders = parseFloat($(target).css("border-right-width"));
            }

            var clientWidth = 0;
            if (window.frameElement && (event.target.tagName === "HTML" || event.target.tagName === "BODY")) {
                clientWidth = document.body.clientWidth;
            } else {
                clientWidth = $(target).prop("clientWidth");
            }
            var scrollBarWidth = $(target).innerWidth() - clientWidth;
            if (scrollBarWidth < 0) {
                scrollBarWidth = 0;
            }
            var minPosResize = posMax - borders - scrollBarWidth - resizeMargin - 1;
            var maxPosResize = posMax + 1;

            var minPosScrollBar = posMax - parseFloat($(target).css("border-right-width")) - scrollBarWidth;
            var maxPosScrollBar = posMax - parseFloat($(target).css("border-right-width"));
            var scrollBarActive = scrollBarWidth > 0 && pageX >= minPosScrollBar && pageX <= maxPosScrollBar;

            if (!mouseLeftDown && $(target).hasClass("oyotarget")) {
                if (pageX >= minPosResize && pageX <= maxPosResize && !scrollBarActive) {
                    $("html").css("cursor", "e-resize");
                    $(target).css("cursor", "e-resize");
                } else {
                    $("html").css("cursor", "default");
                    $(target).css("cursor", "default");
                }
            }

            if (mouseLeftDown && $(target).hasClass("oyolead") && $(target).hasClass("oyoactive")) {
                switch (true) {
                    //case window.frameElement && leadTarget === window.frameElement:
                    case window.frameElement && (event.target.tagName === "HTML" || event.target.tagName === "BODY"):
                        borders = parseFloat($(target).css("border-left-width")) + parseFloat($(target).css("border-right-width")) / 2;
                        break;
                    case $(event.target).hasClass("oyooverlay"):
                        borders = parseFloat($(target).css("border-left-width")) + parseFloat($(target).css("border-right-width")) / 2;
                        borders -= parseFloat($(target).children().last().css("border-right-width")) / 2;
                        break;
                    default:
                        borders = parseFloat($(target).css("border-left-width")) + parseFloat($(target).css("border-right-width")) / 2;
                }

                var width = pageX - $(target).offset().left - borders + 1;
                posChange = width - $(target).innerWidth();
                var newWidth = setWidth(target, width);
                posChange += newWidth - width;
                elementDocument.getSelection().removeAllRanges();
            }
        });

        $(element).each(function (index, target) {
            if (mouseLeftDown && !$(target).hasClass("oyolead") && $(target).hasClass("oyoactive")) {
                var width = $(target).innerWidth() + posChange;
                setWidth(target, width);
            }
        });
        event.stopPropagation();
    }

    function mouseUp(event) {
        $(element).removeClass("oyolead");
        $(element).removeClass("oyoactive");
        if (!$(element).hasClass("oyotarget")) {
            $("html").css("cursor", "default");
        }
    }

    function mouseEnter(event) {
        $(element).addClass("oyotarget");
    }

    function mouseLeave(event) {
        var mouseLeftDown = (event.buttons === 1);
        $(element).removeClass("oyotarget");
        if (!mouseLeftDown) {
            $("html").css("cursor", "default");
        }
    }

    Object.defineProperty(element, "resizeMargin", {
        get: function () {
            return resizeMargin;
        },
        set: function (value) {
            resizeMargin = value;
            $(element).each(function (index, target) {
                setWidth(target);
            });
        }
    });

    element.setSize = function (minX, maxX) {
        $(element).each(function (index, target) {
            $(target).attr("minx", minX);
            $(target).attr("maxx", maxX);
            setWidth(target);
        });
    };

    function setWidth(target, width) {
        if (width === undefined) {
            width = $(target).innerWidth();
        }
        var height = $(target).innerHeight();
        var borders = parseFloat($(target).css("border-left-width")) + parseFloat($(target).css("border-right-width"));
        var minInnerWidth = parseFloat($(target).attr("minx")) - borders;
        var maxInnerWidth = parseFloat($(target).attr("maxx")) - borders;

        switch (true) {
            case width < minInnerWidth:
                width = minInnerWidth;
                break;
            case width > maxInnerWidth:
                width = maxInnerWidth;
                break;
        }
        $(target).innerWidth(width);

        //if (overlay.length > 0) {
        if ($(".oyooverlay", target).parent().length > 0) {
            var overlay = $(".oyooverlay", target);
            $(overlay).outerWidth(resizeMargin);
            $(overlay).outerHeight(height);
            $(target).children().last().outerWidth(width);
            $(target).children().last().outerHeight(height);
        }
        return width;
    }

    return element;

}