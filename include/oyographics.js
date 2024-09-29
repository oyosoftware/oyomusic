/*!
 * oyographics.js 1.0
 * tested with jQuery 3.4.0
 * http://www.oyoweb.nl
 *
 * © 2022 oYoSoftware
 * MIT License
 *
 * oyographics is a set of images that can be scaled without loss of quality
 */

function oyoSpeaker() {
    var svgNS = 'http://www.w3.org/2000/svg';

    var defaultBackgroundColor = "white";
    var defaultFillColor = "#527FC3";

    var graphic = document.createElementNS(svgNS, "svg");
    $(graphic).addClass("oyographic oyospeakericon");
    $(graphic).css("width", 15 + "px");
    $(graphic).css("height", 15 + "px");
    $(graphic).css("background-color", defaultBackgroundColor);
    $(graphic).css("fill", defaultBackgroundColor);
    var circle = document.createElementNS(svgNS, "circle");
    $(circle).addClass("oyofill oyodisplay-1 oyodisplay-3");
    $(circle).attr("cx", 7.5);
    $(circle).attr("cy", 7.5);
    $(circle).attr("r", 6);
    $(graphic).append(circle);
    var circle = document.createElementNS(svgNS, "circle");
    $(circle).addClass("oyodisplay-1 oyodisplay-3");
    $(circle).attr("cx", 7.5);
    $(circle).attr("cy", 7.5);
    $(circle).attr("r", 4.8);
    $(graphic).append(circle);
    var circle = document.createElementNS(svgNS, "circle");
    $(circle).addClass("oyofill oyodisplay-1 oyodisplay-3");
    $(circle).attr("cx", 7.5);
    $(circle).attr("cy", 7.5);
    $(circle).attr("r", 3.6);
    $(graphic).append(circle);
    var rect = document.createElementNS(svgNS, "rect");
    $(rect).attr("x", "1.5");
    $(rect).attr("y", "1.5");
    $(rect).attr("width", "7.2");
    $(rect).attr("height", "12");
    $(graphic).append(rect);
    var rect = document.createElementNS(svgNS, "rect");
    $(rect).addClass("oyofill");
    $(rect).attr("x", "1.5");
    $(rect).attr("y", "5.1");
    $(rect).attr("width", "5.1");
    $(rect).attr("height", "5.1");
    $(graphic).append(rect);
    var polygon = document.createElementNS(svgNS, "polygon");
    $(polygon).addClass("oyofill");
    $(polygon).attr("points", "7.5,1.5 7.5,13.5 2.4,7.5");
    $(graphic).append(polygon);
    var polygon = document.createElementNS(svgNS, "polygon");
    $(polygon).addClass("oyofill oyodisplay-2 oyodisplay-3");
    $(polygon).attr("points", "1.5,2.1 2.1,1.5 13.5,12.9 12.9,13.5");
    $(graphic).append(polygon);
    var polygon = document.createElementNS(svgNS, "polygon");
    $(polygon).addClass("oyodisplay-2 oyodisplay-3");
    $(polygon).attr("points", "2.1,1.5 2.7,0.9 14.1,12.3 13.5,12.9");
    $(graphic).append(polygon);

    $(".oyofill", graphic).css("fill", defaultFillColor);
    oyoGraphicsController(graphic);

    return graphic;
}

function oyoPlayPause() {
    var svgNS = 'http://www.w3.org/2000/svg';

    var defaultBackgroundColor = "white";
    var defaultFillColor = "#527FC3";

    var graphic = document.createElementNS(svgNS, "svg");
    $(graphic).addClass("oyographic oyoplaypauseicon");
    $(graphic).css("width", 15 + "px");
    $(graphic).css("height", 15 + "px");
    $(graphic).css("background-color", defaultBackgroundColor);
    $(graphic).css("fill", defaultBackgroundColor);
    var polygon = document.createElementNS(svgNS, "polygon");
    $(polygon).addClass("oyofill oyodisplay-0");
    $(polygon).attr("points", "6,3 6,12 12,7.5");
    $(graphic).append(polygon);
    var rect = document.createElementNS(svgNS, "rect");
    $(rect).addClass("oyofill oyodisplay-1");
    $(rect).attr("x", "4.5");
    $(rect).attr("y", "3.75");
    $(rect).attr("width", "2.25");
    $(rect).attr("height", "7.5");
    $(graphic).append(rect);
    var rect = document.createElementNS(svgNS, "rect");
    $(rect).addClass("oyofill oyodisplay-1");
    $(rect).attr("x", "8.25");
    $(rect).attr("y", "3.75");
    $(rect).attr("width", "2.25");
    $(rect).attr("height", "7.5");
    $(graphic).append(rect);

    $(".oyofill", graphic).css("fill", defaultFillColor);
    oyoGraphicsController(graphic);

    return graphic;
}

function oyoNote() {
    var svgNS = 'http://www.w3.org/2000/svg';

    var defaultBackgroundColor = "white";
    var defaultFillColor = "#527FC3";

    var graphic = document.createElementNS(svgNS, "svg");
    $(graphic).addClass("oyographic oyonoteicon");
    $(graphic).css("width", 15 + "px");
    $(graphic).css("height", 15 + "px");
    $(graphic).css("background-color", defaultBackgroundColor);
    $(graphic).css("fill", defaultBackgroundColor);
    var rect = document.createElementNS(svgNS, "rect");
    $(rect).addClass("oyofill");
    $(rect).attr("x", 4.4);
    $(rect).attr("y", 3.2);
    $(rect).attr("width", 1.6);
    $(rect).attr("height", 8.8);
    $(graphic).append(rect);
    var rect = document.createElementNS(svgNS, "rect");
    $(rect).addClass("oyofill");
    $(rect).attr("x", 10.6);
    $(rect).attr("y", 1.4);
    $(rect).attr("width", 1.6);
    $(rect).attr("height", 8.8);
    $(graphic).append(rect);
    var polygon = document.createElementNS(svgNS, "polygon");
    $(polygon).addClass("oyofill");
    $(polygon).attr("points", "4.4,2.5,12.2,0.7,12.2,3.9,4.4,5.7");
    $(graphic).append(polygon);
    var ellipse = document.createElementNS(svgNS, "ellipse");
    $(ellipse).addClass("oyofill");
    $(ellipse).attr("cx", 3.5);
    $(ellipse).attr("cy", 12.0);
    $(ellipse).attr("rx", 2.5);
    $(ellipse).attr("ry", 1.8);
    $(ellipse).attr("transform", "rotate(-15, 6, 12)");
    $(graphic).append(ellipse);
    var ellipse = document.createElementNS(svgNS, "ellipse");
    $(ellipse).addClass("oyofill");
    $(ellipse).attr("cx", 9.7);
    $(ellipse).attr("cy", 10.2);
    $(ellipse).attr("rx", 2.5);
    $(ellipse).attr("ry", 1.8);
    $(ellipse).attr("transform", "rotate(-15, 12.2, 10.2)");
    $(graphic).append(ellipse);
    $(".oyofill", graphic).css("fill", defaultFillColor);
    oyoGraphicsController(graphic);

    return graphic;
}

function oyoGraphicsController(element) {
    if (element.tagName.toUpperCase() !== "SVG") {
        return;
    }

    var defaultBackgroundColor = "white";
    var defaultFillColor = "#527FC3";

    element.changeState = function (state = 0) {
        var displayElements = $("[class*=oyodisplay-]", element);
        displayElements.each(function (index, displayElement) {
            $(displayElement).css("display", "none");
            var className = "oyodisplay-" + state;
            if ($(displayElement).hasClass(className)) {
                $(displayElement).css("display", "inline-block");
            }
        });
    };

    element.changeState();

    element.scale = function (scale = 1) {
        var width = scale * 15;
        var height = scale * 15;
        $(element).width(width);
        $(element).height(height);
        var elements = $(element).find("*");
        var svgCSSScale = "scale(" + scale + ")";
        $(elements).each(function (index, element) {
            var svgCSSTransform = svgCSSScale;
            var transform = $(element).attr("transform");
            if (transform !== undefined && transform !== "none") {
                var scalePos = transform.indexOf("scale");
                if (scalePos !== -1) {
                    var scaleEndPos = transform.indexOf(")", scalePos);
                    transform = transform.substring(scaleEndPos + 2);
                }
            }
            if (transform !== undefined) {
                svgCSSTransform = svgCSSScale + " " + transform;
            }
            $(element).attr("transform", svgCSSTransform);
        });
    };

    element.changeBackgroundColor = function (color) {
        $(element).css("background-color", color);
        $(element).css("fill", color);
    };

    element.changeFillColor = function (color) {
        $(".oyofill", element).css("fill", color);
    };

    element.resetColors = function () {
        $(element).css("background-color", defaultBackgroundColor);
        $(element).css("fill", defaultBackgroundColor);
        $(".oyofill", element).css("fill", defaultFillColor);
    };

}
