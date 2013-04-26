M.dualpane = M.dualpane || {};

M.dualpane.init = function (Y){

    //frame = Y.one('#right');
    //html = '<iframe title="Dual Pane Window"></iframe>';
    //frame.set('innerHTML', html);

    var height = Y.one('body').get('winHeight');
    var leftpane = Y.one('.dualpane_left');
    var rightpane = Y.one('.dualpane_right');
    height -= rightpane.getXY()[1];
    height -= 10;
    rightpane.setStyle('height', height + 'px');
    leftpane.setStyle('height', height + 'px');

    var oContentArea = null;
    var sRootPath = null;
    var bHasScrollStyle = false;

    initialize();
    var starturl = document.getElementById("starturl").value;
    //sizechange(starturl);

    function initialize() {
        oContentArea = document.getElementById("resourceobject");
        var frame = Y.one('iframe');
        if(frame == null){
            initializeContentArea();
            setContentHeight();
        }
    }

    function initializeContentArea() {
        sRootPath = oContentArea.data.replace(/[^\/]*$/, "");

        // Check if object has scrollbar with changeable style.
        if ((oContentArea.body != null) && (oContentArea.body.scroll != null)) {
            bHasScrollStyle = true;
        }

        setContentAreaStyle();
    }

    // Set style for object tag.
    function setContentAreaStyle() {
        if (bHasScrollStyle) {
            if ((oContentArea.body != null) && (oContentArea.body.scroll != null)) {
                bHasScrollStyle = true;
                oContentArea.body.scroll = "auto";
                oContentArea.body.style.scrollbarFaceColor = "#005A9C";
                oContentArea.body.style.scrollbarArrowColor = "#005A9C";
                oContentArea.body.style.scrollbarTrackColor = "#005A9C";
                oContentArea.body.style.scrollbarShadowColor = "#005A9C";
                oContentArea.body.style.scrollbarHighlightColor = "#005A9C";
                oContentArea.body.style.scrollbar3dlightColor = "#005A9C";
                oContentArea.body.style.scrollbarDarkshadowColor = "#005A9C";
            } else {
                setTimeout("setContentAreaStyle();", 10);
            }
        }
    }

    function setContentHeight() {
        var height = Y.one('body').get('winHeight');
        oContentArea.removeAttribute("width");
        oContentArea.removeAttribute("height");
        Y.one('#resourceobject').setStyle('height', height + 'px');
        Y.one('#resourceobject').setStyle('width', '100%');
    }

    function sizechange(url){
        var frame = Y.one('iframe');
        if(frame == null){
            oNew = document.createElement('object');
            oNew.setAttribute('id', 'resourceobject');
            oNew.setAttribute('type', 'text/html');
            oNew.setAttribute('data', url);
            oNew.setAttribute('style', 'width: 100%;');
            oNewParam = document.createElement('param');
            oNewParam.setAttribute('name', 'src');
            oNewParam.setAttribute('value', url);
            oNew.appendChild(oNewParam);
            var oPlaceHolder = oContentArea.parentNode;
            oPlaceHolder.removeChild(oContentArea);
            oPlaceHolder.appendChild(oNew);
            oContentArea = oNew;
            setContentAreaStyle();
            setContentHeight();
            return false;
        } else {
            frame.set('src', url);
            var height = Y.one('body').get('winHeight');
            height -= frame.getXY()[1];
            frame.setStyle('height', height + 'px');
        }
    }

    changeframe = function(e){
        link = e.target;
        var url = link.getAttribute ('href');
        sizechange(url);
    }

    Y.on('click', changeframe, '.dualpane_rightpane_link');

}

M.dualpanestepsscreen = M.dualpanesteps || {};

M.dualpanestepsscreen.init = function (Y) {
    var backstep = Y.one('.dualpane_backsteplink');
    var nextstep = Y.one('.dualpane_nextsteplink');
    var currentstepno = 0;
    var laststepno = 0;
    var dualpanesteps = document.querySelectorAll('.dualpane_step_hidden');

    initialize();
    function initialize() {
        displaystep();
    }

    downstep = function(e) {
        if (currentstepno == 0) {
            return;
        }
        laststepno = currentstepno;
        currentstepno--;
        greyout();
        displaystep();
    }
    upstep = function(e) {
        if (currentstepno == dualpanesteps.length-1) {
            return;
        }
        laststepno = currentstepno;
        currentstepno++;
        greyout();
        displaystep();
    }

    function greyout() {
        if (currentstepno == 0) {
            Y.one('#dualpane_backsteplink_grey').setStyle('display', 'inline');
            Y.one('#dualpane_backsteplink').setStyle('display', 'none');
        } else {
            Y.one('#dualpane_backsteplink_grey').setStyle('display', 'none');
            Y.one('#dualpane_backsteplink').setStyle('display', 'inline');
        }
        if (currentstepno == dualpanesteps.length-1) {
            Y.one('#dualpane_nextsteplink_grey').setStyle('display', 'inline');
            Y.one('#dualpane_nextsteplink').setStyle('display', 'none');
        } else {
            Y.one('#dualpane_nextsteplink_grey').setStyle('display', 'none');
            Y.one('#dualpane_nextsteplink').setStyle('display', 'inline');
        }
    }
    function displaystep() {
        Y.one('#dualpane_step_'+laststepno).setStyle('display', 'none');
        Y.one('#dualpane_step_'+currentstepno).setStyle('display', 'block');
    }
    function gotostart() {
        if (dualpanesteps.length == 0) {
            return;
        }
        laststepno = currentstepno;
        currentstepno = 0;
        greyout();
        displaystep();
    }
    Y.on('click', downstep, '#dualpane_backsteplink');
    Y.on('click', upstep, '#dualpane_nextsteplink');
    Y.on('click', gotostart, '.dualpane_startlink');
}