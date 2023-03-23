/*
 * oyoprogress.js 1.0
 * tested with jQuery 3.4.0
 * http://www.oyoweb.nl
 *
 * © 2022 oYoSoftware
 * MIT License
 *
 * oyoprogress is a tool to define an alternative input progress element which is browser independant
 */

function oyoProgress(progressWidth, progressHeight, progressBorderRadius) {

    var defaultProgressColor = "#B3CEB3";
    var defaultProgressBarColor = "#527FC3";
    var defaultTextColor = "black";
    var defaultTextBarColor = "white";
    var defaultBorder = "1px solid black";
    var progressColor = defaultProgressColor;
    var progressBarColor = defaultProgressBarColor;

    var progressMin = 0;
    var progressMax = 100;
    var progressStart = 0;
    var progressEnd = 100;
    var progressValue = 0;
    var progressText = "&percentage Complete";
    var progressUnit = "";
    var showLimits = false;

    var progress = document.createElement("div");
    $(progress).addClass("oyoprogress");
    $(progress).css("box-sizing", "border-box");
    $(progress).css("border", defaultBorder);
    $(progress).outerWidth(300);
    $(progress).outerHeight(20);
    var progressInnerWidth = $(progress).innerWidth();
    var progressInnerHeight = $(progress).innerHeight();
    $(progress).css("border-radius", progressBorderRadius);
    $(progress).css("text-align", "center");
    $(progress).css("font-weight", "bold");
    $(progress).css("overflow", "hidden");

// Line
    var progressline = document.createElement("div");
    $(progressline).attr("class", "oyoprogressline");
    $(progressline).outerWidth(progressInnerWidth);
    $(progressline).outerHeight(progressInnerHeight);
    $(progressline).css("border", "0px");
    $(progressline).css("background-color", defaultProgressColor);
    $(progressline).css("text-align", "center");
    $(progressline).css("overflow", "hidden");
    $(progress).append(progressline);

    var progresslinetext = document.createElement("div");
    $(progresslinetext).attr("class", "oyoprogresslinetext");
    $(progresslinetext).outerWidth(progressInnerWidth);
    $(progresslinetext).css("border", "0px");
    $(progresslinetext).css("color", defaultTextColor);
    $(progresslinetext).css("position", "relative");
    $(progresslinetext).css("top", "50%");
    $(progresslinetext).css("transform", "translateY(-50%)");
    $(progressline).append(progresslinetext);

    var progresslinestart = document.createElement("div");
    $(progresslinestart).attr("class", "oyoprogresslinestart");
    $(progresslinestart).outerWidth(progressInnerWidth);
    $(progresslinestart).outerHeight(progressInnerHeight);
    $(progresslinestart).css("border", "0px");
    $(progresslinestart).css("position", "relative");
    $(progresslinestart).css("top", -1 * progressInnerHeight);
    $(progresslinestart).css("overflow", "hidden");
    $(progresslinestart).css("visibility", "hidden");
    $(progress).append(progresslinestart);

    var progresslinestarttext = document.createElement("div");
    $(progresslinestarttext).attr("class", "oyoprogresslinestarttext");
    $(progresslinestarttext).css("display", "inline-block");
    $(progresslinestarttext).css("border", "0px");
    $(progresslinestarttext).css("color", defaultTextColor);
    $(progresslinestarttext).css("position", "absolute");
    $(progresslinestarttext).css("transform", "translateY(-50%)");
    $(progresslinestarttext).css("white-space", "nowrap");
    $(progresslinestart).append(progresslinestarttext);

    var progresslineend = document.createElement("div");
    $(progresslineend).attr("class", "oyoprogresslineend");
    $(progresslineend).outerWidth(progressInnerWidth);
    $(progresslineend).outerHeight(progressInnerHeight);
    $(progresslineend).css("border", "0px");
    $(progresslineend).css("position", "relative");
    $(progresslineend).css("top", -2 * progressInnerHeight);
    $(progresslineend).css("overflow", "hidden");
    $(progresslineend).css("visibility", "hidden");
    $(progress).append(progresslineend);

    var progresslineendtext = document.createElement("div");
    $(progresslineendtext).attr("class", "oyoprogresslineendtext");
    $(progresslineendtext).css("display", "inline-block");
    $(progresslineendtext).css("border", "0px");
    $(progresslineendtext).css("color", defaultTextColor);
    $(progresslineendtext).css("position", "absolute");
    $(progresslineendtext).css("transform", "translateY(-50%)");
    $(progresslineendtext).css("white-space", "nowrap");
    $(progresslineend).append(progresslineendtext);

// Bar
    var progressbar = document.createElement("div");
    $(progressbar).attr("class", "oyoprogressbar");
    $(progressbar).outerWidth(0);
    $(progressbar).outerHeight(progressInnerHeight);
    $(progressbar).css("border", "0px");
    $(progressbar).css("background-color", defaultProgressBarColor);
    $(progressbar).css("text-align", "center");
    $(progressbar).css("position", "relative");
    $(progressbar).css("top", -3 * progressInnerHeight);
    $(progressbar).css("overflow", "hidden");
    $(progress).append(progressbar);

    var progressbartext = document.createElement("div");
    $(progressbartext).attr("class", "oyoprogressbartext");
    $(progressbartext).outerWidth(progressInnerWidth);
    $(progressbartext).css("border", "0px");
    $(progressbartext).css("color", defaultTextBarColor);
    $(progressbartext).css("position", "relative");
    $(progressbartext).css("top", "50%");
    $(progressbartext).css("transform", "translateY(-50%)");
    $(progressbar).append(progressbartext);

    var progressbarstart = document.createElement("div");
    $(progressbarstart).attr("class", "oyoprogressbarstart");
    $(progressbarstart).outerWidth(0);
    $(progressbarstart).outerHeight(progressInnerHeight);
    $(progressbarstart).css("border", "0px");
    $(progressbarstart).css("position", "relative");
    $(progressbarstart).css("top", -4 * progressInnerHeight);
    $(progressbarstart).css("overflow", "hidden");
    $(progressbarstart).css("visibility", "hidden");
    $(progress).append(progressbarstart);

    var progressbarstarttext = document.createElement("div");
    $(progressbarstarttext).attr("class", "oyoprogressbarstarttext");
    $(progressbarstarttext).css("display", "inline-block");
    $(progressbarstarttext).css("border", "0px");
    $(progressbarstarttext).css("color", defaultTextBarColor);
    $(progressbarstarttext).css("position", "absolute");
    $(progressbarstarttext).css("transform", "translateY(-50%)");
    $(progressbarstarttext).css("white-space", "nowrap");
    $(progressbarstart).append(progressbarstarttext);

    var progressbarend = document.createElement("div");
    $(progressbarend).attr("class", "oyoprogressbarend");
    $(progressbarend).outerWidth(0);
    $(progressbarend).outerHeight(progressInnerHeight);
    $(progressbarend).css("border", "0px");
    $(progressbarend).css("position", "relative");
    $(progressbarend).css("top", -5 * progressInnerHeight);
    $(progressbarend).css("overflow", "hidden");
    $(progressbarend).css("visibility", "hidden");
    $(progress).append(progressbarend);

    var progressbarendtext = document.createElement("div");
    $(progressbarendtext).attr("class", "oyoprogressbarendtext");
    $(progressbarendtext).css("display", "inline-block");
    $(progressbarendtext).css("border", "0px");
    $(progressbarendtext).css("color", defaultTextBarColor);
    $(progressbarendtext).css("position", "absolute");
    $(progressbarendtext).css("transform", "translateY(-50%)");
    $(progressbarendtext).css("white-space", "nowrap");
    $(progressbarend).append(progressbarendtext);

// Limit
    var progresslimit = document.createElement("div");
    $(progresslimit).attr("class", "oyoprogresslimit");
    $(progresslimit).outerWidth(0);
    $(progresslimit).height(progressInnerHeight);
    $(progresslimit).css("border", "0px");
    $(progresslimit).css("background-color", defaultProgressColor);
    $(progresslimit).css("text-align", "center");
    $(progresslimit).css("position", "relative");
    $(progresslimit).css("top", -6 * progressInnerHeight);
    $(progresslimit).css("overflow", "hidden");
    $(progress).append(progresslimit);

    var progresslimittext = document.createElement("div");
    $(progresslimittext).attr("class", "oyoprogresslimittext");
    $(progresslimittext).outerWidth(progressInnerWidth);
    $(progresslimittext).css("border", "0px");
    $(progresslimittext).css("color", defaultTextColor);
    $(progresslimittext).css("position", "relative");
    $(progresslimittext).css("top", "50%");
    $(progresslimittext).css("transform", "translateY(-50%)");
    $(progresslimit).append(progresslimittext);

    var progresslimitstart = document.createElement("div");
    $(progresslimitstart).attr("class", "oyoprogresslimitstart");
    $(progresslimitstart).outerWidth(0);
    $(progresslimitstart).outerHeight(progressInnerHeight);
    $(progresslimitstart).css("border", "0px");
    $(progresslimitstart).css("position", "relative");
    $(progresslimitstart).css("top", -7 * progressInnerHeight);
    $(progresslimitstart).css("overflow", "hidden");
    $(progresslimitstart).css("visibility", "hidden");
    $(progress).append(progresslimitstart);

    var progresslimitstarttext = document.createElement("div");
    $(progresslimitstarttext).attr("class", "oyoprogresslimitstarttext");
    $(progresslimitstarttext).css("display", "inline-block");
    $(progresslimitstarttext).css("border", "0px");
    $(progresslimitstarttext).css("color", defaultTextColor);
    $(progresslimitstarttext).css("position", "absolute");
    $(progresslimitstarttext).css("transform", "translateY(-50%)");
    $(progresslimitstarttext).css("white-space", "nowrap");
    $(progresslimitstart).append(progresslimitstarttext);

    change(progressWidth, progressHeight, progressBorderRadius);

    if (progressBorderRadius === undefined) {
        progressBorderRadius = $(progress).outerHeight() / 2;
        $(progress).css("border-radius", progressBorderRadius);
    }

    progress.changeValue = function (value, start = progressStart, end = progressEnd) {
        if (value < start) {
            value = start;
        }
        if (value > end) {
            value = end;
        }
        var percentage = 100 * (value - progressMin) / (progressMax - progressMin);
        percentage = Math.floor(percentage);
        var text = progressText;
        text = text.replaceAll(" ", "&nbsp;");
        text = text.replaceAll("&percentage", percentage + "%");
        text = text.replaceAll("&min", progressMin + progressUnit);
        text = text.replaceAll("&max", progressMax + progressUnit);
        text = text.replaceAll("&start", progressStart + progressUnit);
        text = text.replaceAll("&end", progressEnd + progressUnit);
        text = text.replaceAll("&value", value + progressUnit);
        var barWidth = (value - progressMin) / (progressMax - progressMin);
        $(progressbar).width(100 * barWidth + "%");
        $(progressbarstart).width(100 * barWidth + "%");
        $(progressbarend).width(100 * barWidth + "%");
        $(progresslinetext).html(text);
        $(progressbartext).html(text);
        $(progresslimittext).html(text);
    };

    Object.defineProperty(progress, "showLimits", {
        get: function () {
            return showLimits;
        },
        set: function (value) {
            showLimits = value;
            if (showLimits) {
                var fontSize = parseFloat($(progress).css("font-size"));
                var height = $(progress).outerHeight();
                var percentage = 100 * ((height - 2 * fontSize) / 3 + 0.5 * fontSize) / height;
                $(progresslinetext).css("top", (100 - percentage) + "%");
                $(progressbartext).css("top", (100 - percentage) + "%");
                $(progresslimittext).css("top", (100 - percentage) + "%");
                $(progresslinestarttext).css("top", percentage + "%");
                $(progressbarstarttext).css("top", percentage + "%");
                $(progresslimitstarttext).css("top", percentage + "%");
                $(progresslineendtext).css("top", percentage + "%");
                $(progressbarendtext).css("top", percentage + "%");
                $(progresslinestart).css("visibility", "visible");
                $(progressbarstart).css("visibility", "visible");
                $(progresslimitstart).css("visibility", "visible");
                $(progresslineend).css("visibility", "visible");
                $(progressbarend).css("visibility", "visible");
            } else {
                $(progresslinetext).css("top", "50%");
                $(progressbartext).css("top", "50%");
                $(progresslimittext).css("top", "50%");
                $(progresslinestart).css("visibility", "hidden");
                $(progressbarstart).css("visibility", "hidden");
                $(progresslimitstart).css("visibility", "hidden");
                $(progresslineend).css("visibility", "hidden");
                $(progressbarend).css("visibility", "hidden");
            }
        }
    });

    Object.defineProperty(progress, "min", {
        get: function () {
            return progressMin;
        },
        set: function (value) {
            progressMin = value;
            progressStart = value;
        }
    });

    Object.defineProperty(progress, "max", {
        get: function () {
            return progressMax;
        },
        set: function (value) {
            progressMax = value;
            progressEnd = value;
        }
    });

    function changeLimits() {
        $(progresslinestarttext).html(progressStart + progressUnit);
        $(progresslineendtext).html(progressEnd + progressUnit);
        $(progressbarstarttext).html(progressStart + progressUnit);
        $(progressbarendtext).html(progressEnd + progressUnit);
        $(progresslimitstarttext).html(progressStart + progressUnit);
        var limitStartWidth = (progressStart - progressMin) / (progressMax - progressMin) * $(progress).innerWidth();
        var limitEndWidth = (progressEnd - progressMin) / (progressMax - progressMin) * $(progress).innerWidth();
        $(progresslimit).width(limitStartWidth);
        $(progresslimitstart).width(limitStartWidth);
        var textStartWidth = $(progressbarstarttext).outerWidth();
        var left = limitStartWidth - 0.5 * textStartWidth;
        var minLeft = 4;
        if (left < minLeft) {
            left = minLeft;
        }
        $(progresslinestarttext).css("left", left);
        $(progressbarstarttext).css("left", left);
        $(progresslimitstarttext).css("left", left);
        var textEndWidth = $(progressbarendtext).outerWidth();
        var left = limitEndWidth - 0.5 * textEndWidth;
        var maxLeft = $(progress).innerWidth() - textEndWidth - 4;
        if (left > maxLeft) {
            left = maxLeft;
        }
        $(progresslineendtext).css("left", left);
        $(progressbarendtext).css("left", left);
    }

    Object.defineProperty(progress, "start", {
        get: function () {
            return progressStart;
        },
        set: function (value) {
            if (value < progressMin) {
                value = progressMin;
            }
            progressStart = value;
            if (progressEnd > progressMax) {
                progressEnd = progressMax;
            }
            changeLimits();
        }
    });

    Object.defineProperty(progress, "end", {
        get: function () {
            return progressEnd;
        },
        set: function (value) {
            if (value > progressMax) {
                value = progressMax;
            }
            progressEnd = value;
            if (progressStart < progressMin) {
                progressStart = progressMin;
            }
            changeLimits();
        }
    });

    Object.defineProperty(progress, "value", {
        get: function () {
            return progressValue;
        },
        set: function (value) {
            if (value < progressStart) {
                value = progressStart;
            }
            if (value > progressEnd) {
                value = progressEnd;
            }
            progressValue = value;
            progress.changeValue(progressValue);
        }
    });

    progress.value = progressMin;

    Object.defineProperty(progress, "text", {
        get: function () {
            return progressText;
        },
        set: function (value) {
            progressText = value;
            progress.value = progressValue;
        }
    });

    Object.defineProperty(progress, "unit", {
        get: function () {
            return progressUnit;
        },
        set: function (value) {
            progressUnit = value;
        }
    });

    progress.changeColors = function (color = defaultProgressColor, barColor = defaultProgressBarColor) {
        progressColor = color;
        progressBarColor = barColor;
        $(progressline).css("background-color", progressColor);
        $(progressbar).css("background-color", progressBarColor);
        $(progresslimit).css("background-color", progressColor);
    };

    progress.changeTextColors = function (textColor = progressBarColor, barTextColor = progressColor) {
        $(progresslinetext).css("color", textColor);
        $(progresslinestarttext).css("color", textColor);
        $(progresslineendtext).css("color", textColor);
        $(progressbartext).css("color", barTextColor);
        $(progressbarstarttext).css("color", barTextColor);
        $(progressbarendtext).css("color", barTextColor);
        $(progresslimittext).css("color", textColor);
        $(progresslimitstarttext).css("color", textColor);
    };

    progress.changeTextStroke = function (width = 1, textStrokeColor = defaultTextBarColor, barTextStrokeColor = defaultTextColor) {
        $(progress).css("-webkit-text-stroke-width", width + "px");
        $(progresslinetext).css("-webkit-text-stroke-color", textStrokeColor);
        $(progresslinestarttext).css("-webkit-text-stroke-color", textStrokeColor);
        $(progresslineendtext).css("-webkit-text-stroke-color", textStrokeColor);
        $(progressbartext).css("-webkit-text-stroke-color", barTextStrokeColor);
        $(progressbarstarttext).css("-webkit-text-stroke-color", barTextStrokeColor);
        $(progressbarendtext).css("-webkit-text-stroke-color", barTextStrokeColor);
        $(progresslimittext).css("-webkit-text-stroke-color", textStrokeColor);
        $(progresslimitstarttext).css("-webkit-text-stroke-color", textStrokeColor);
    };

    progress.resetColors = function () {
        $(progressline).css("background-color", defaultProgressColor);
        $(progressbar).css("background-color", defaultProgressBarColor);
        $(progresslimit).css("background-color", defaultProgressColor);
        $(progresslinetext).css("color", defaultTextColor);
        $(progresslinestarttext).css("color", defaultTextColor);
        $(progressbartext).css("color", defaultTextBarColor);
        $(progressbarstarttext).css("color", defaultTextBarColor);
        $(progresslimittext).css("color", defaultTextColor);
        $(progresslimitstarttext).css("color", defaultTextColor);
        $(progress).css("-webkit-text-stroke-width", 0 + "px");
        $(progresslinetext).css("-webkit-text-stroke-color", defaultTextBarColor);
        $(progresslinestarttext).css("-webkit-text-stroke-color", defaultTextBarColor);
        $(progressbartext).css("-webkit-text-stroke-color", defaultTextColor);
        $(progressbarstarttext).css("-webkit-text-stroke-color", defaultTextColor);
        $(progresslimittext).css("-webkit-text-stroke-color", defaultTextBarColor);
        $(progresslimitstarttext).css("-webkit-text-stroke-color", defaultTextBarColor);
    };

    function change(width, height, borderRadius) {
        if (width !== undefined) {
            progressWidth = width;
            $(progress).outerWidth(progressWidth);
            var progressInnerWidth = $(progress).innerWidth();
            $(progressline).outerWidth(progressInnerWidth);
            $(progresslinetext).outerWidth(progressInnerWidth);
            $(progresslinestart).outerWidth(progressInnerWidth);
            $(progresslineend).outerWidth(progressInnerWidth);
            $(progresslinetext).outerWidth(progressInnerWidth);
            $(progressbartext).outerWidth(progressInnerWidth);
            $(progresslimittext).outerWidth(progressInnerWidth);
            // This looks funny, but actually it resizes itself
            progress.start = progressStart;
            progress.end = progressEnd;
        }
        if (height !== undefined) {
            progressHeight = height;
            $(progress).outerHeight(progressHeight);
            var progressInnerHeight = $(progress).innerHeight();
            $(progressline).outerHeight(progressInnerHeight);
            $(progresslinestart).outerHeight(progressInnerHeight);
            $(progresslinestart).css("top", -1 * progressInnerHeight + "px");
            $(progresslineend).outerHeight(progressInnerHeight);
            $(progresslineend).css("top", -2 * progressInnerHeight + "px");
            $(progressbar).outerHeight(progressInnerHeight);
            $(progressbar).css("top", -3 * progressInnerHeight + "px");
            $(progressbarstart).outerHeight(progressInnerHeight);
            $(progressbarstart).css("top", -4 * progressInnerHeight + "px");
            $(progressbarend).outerHeight(progressInnerHeight);
            $(progressbarend).css("top", -5 * progressInnerHeight + "px");
            $(progresslimit).outerHeight(progressInnerHeight);
            $(progresslimit).css("top", -6 * progressInnerHeight + "px");
            $(progresslimitstart).outerHeight(progressInnerHeight);
            $(progresslimitstart).css("top", -7 * progressInnerHeight + "px");
        }
        if (borderRadius !== undefined) {
            progressBorderRadius = borderRadius;
            $(progress).css("border-radius", borderRadius);
        }
    }

    progress.change = change;

    progress.changeFont = function (family, size, weight) {
        if (family !== undefined) {
            $(progress).css("font-family", family);
        }
        if (size !== undefined) {
            $(progress).css("font-size", size);
        }
        if (weight !== undefined) {
            $(progress).css("font-weight", weight);
        }
    };

    progress.changeBorder = function (border) {
        $(progress).css("border", border);
        progress.change(progressWidth, progressHeight);
        if (progress.showLimits) {
            progress.showLimits = true;
        }
    };

    return progress;
}
