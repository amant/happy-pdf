(function () {
  "use strict";
}());

/**
 * Clipboard copy and paste
 *
 * eg:
 * $(document).on('copy', function(evt) { clipboard.copyText('hello-world'); });
 *
 * $(document).on('paste', function(evt) {
 *  clipboard.pasteText(function (pasted_text) {
 *   console.log(pasted_text);
 *  });
 * });
 *
 */
function Clipboard() {
  this._saveSelection = false;

  this._restoreSelection = function () {
    if (this._saveSelection) {
      window.getSelection().removeAllRanges();

      for (var i = 0; i < this._saveSelection.length; i++)
      window.getSelection().addRange(this._saveSelection[i]);

      this._saveSelection = false;
    }
  };

  /**
   * On key down handler create "pre" tag. Set content to copy to this tag. Make Selection on this tag and return true in handler.
   * This call standard handler of chrome and copied selected text. And if u need u may be set timeout for function for restoring previous selection.
   */
  this.copyText = function (text) {
    var div = document.getElementById('clipboard-copy');

    if (!div) {
      div = document.createElement('pre');
      div.setAttribute('id', 'clipboard-copy');
      div.setAttribute('style', 'opacity: 0; position: absolute; top: -10000px; right: 0;');
      document.body.appendChild(div);
    }

    div.textContent = text;

    var range = document.createRange();
    range.selectNodeContents(div);

    this._saveSelection = [];

    var selection = window.getSelection();

    for (var i = 0; i < selection.rangeCount; i++)
    this._saveSelection[i] = selection.getRangeAt(i);

    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);

    setTimeout(this._restoreSelection.bind(this), 100);
  };

  this.pasteText = function (callback) {
    var div = document.getElementById('clipboard-paste');

    if (!div) {
      div = document.createElement('textarea');
      div.setAttribute('id', 'clipboard-paste');
      div.setAttribute('style', 'opacity: 0;position: absolute;top: -10000px;right: 0;');
      document.body.appendChild(div);

      function keyupHandler() {
        if (this.callback) {
          this.pastedText = $('#clipboard-paste').val();

          this.callback(this.pastedText);
          this.callback = false;
          this.pastedText = false;

          div.removeEventListener('keyup', keyupHandler);

          div.parentNode.removeChild(div);

          setTimeout(this._restoreSelection.bind(this), 100);
        }
      };

      div.addEventListener('keyup', keyupHandler.bind(this));
    }

    var range = document.createRange();
    range.selectNodeContents(div);

    this._saveSelection = [];
    var selection = window.getSelection();

    for (var i = 0; i < selection.rangeCount; i++)
    this._saveSelection[i] = selection.getRangeAt(i);

    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);

    div.focus();

    this.callback = callback;
  }
}

//-------------------- happyPDF Framework
(function (win) {
  var global = win;
  var doc = global.doc;

  var happyPDF = function (win, doc) {
    //return new HappyPDF(doc);
  };

  happyPDF.getWin = function () {
    return win;
  };

  happyPDF.getDoc = function () {
    return win.document;
  };

  happyPDF.baseURI = function () {
    return 'http://' + location.hostname + '/happy-pdf/';
    //return 'http://localhost/happy-pdf/';
  }

  var HappyPDF = function (win, doc) {};

  // initialize clipboard
  happyPDF.clipboard = new Clipboard();

  // expose to global scope
  global.happyPDF = happyPDF;
  global.$h = happyPDF;

  // shortcut to prototype
  happyPDF.fn = HappyPDF.prototype;
}(window));


//-------------------- [start] XML helper

// Read xml doc
happyPDF.loadXMLDoc = function (xmldoc) {
  var xhttp;

  if (window.XMLHttpRequest) {
    xhttp = new XMLHttpRequest();
  } else {
    xhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }

  xhttp.open("GET", xmldoc, false);
  xhttp.send("");

  return xhttp.responseXML;
};

// Load xml string
happyPDF.loadXMLString = function (xmlstr) {
  var result = '',
    parser = '';
  if (window.DOMParser) {
    parser = new DOMParser();
    result = parser.parseFromString(xmlstr, "text/xml");
  } else { // IE
    result = new ActiveXObject("Microsoft.XMLDOM");
    result.async = false;
    result.loadXML(xmlstr);
  }

  return result;
};

// Apply xslt tranformation to xml
happyPDF.applyXSLT = function (xml, xsl) {
  var result = null;

  if (window.ActiveXObject) {
    result = xml.transformNode(xsl);

  } else if (document.implementation && document.implementation.createDocument) {
    var xsltProcessor = new XSLTProcessor();
    xsltProcessor.importStylesheet(xsl);
    result = xsltProcessor.transformToFragment(xml, document);

  } else {
    alert("Browser don't support XSLT. Update browser or use different browser (eg: firefox, chrome).");
  }

  return result;
};

//-------------------- [end] XML helper


//-------------------- [start] FRP helper

happyPDF.frp = (function () {
  var list = {};

  return {
    observable: function (init) {
      var x = init;

      return {
        get: function () {
          return x;
        },
        set: function (val) {
          x = val;
        },
        declare: function (val) {
          x = val;
        }
      };
    },
    setListener: function (obj, callback) {
      if (obj.hasOwnProperty('set')) {
        var currentSetter = obj.set;
        list[callback] = currentSetter;

        obj.set = function (val) {
          var oldVal = obj.get();
          if (callback(val, oldVal) !== false) currentSetter.call(obj, val);
        };
      }
    },

    removeListener: function (obj, callback) {
      if (obj.hasOwnProperty('set')) {
        obj.set = list[callback];
        list[callback] = undefined;
      }
    }
  };
}());

//-------------------- [end] FRP helper

//-------------------- [start] Page helper

/**
 * Returns width/height of document body
 */
happyPDF.getViewportSize = function () {
  var el = happyPDF.getDoc().body;
  return {
    width: el.clientWidth,
    height: el.clientHeight
  };
};

/**
 * calculate the width/height of thumb-pane, content-pane and controlbar
 */
happyPDF.resizeWindowHandler = function () {
  var viewportSize = happyPDF.getViewportSize();
  var viewEl = document.getElementById('view');

  var left = viewEl.offsetLeft,
    top = viewEl.offsetTop,
    divW = viewportSize.width,
    divH = viewportSize.height;

  var thumbPaneEl = document.getElementById('thumb-pane');
  var thumbW = thumbPaneEl.offsetWidth,
    thumbL = divW - thumbW;

  var pageW = thumbL - left;

  // set content-pane's style:left
  var contentPaneEl = document.getElementById('content-pane');
  contentPaneEl.style.left = left + thumbW + 'px';

  var controlbarEl = document.getElementById('controlbar');
  var controlbarH = controlbarEl.offsetHeight;

  // set controlbar's width and height
  contentPaneEl.style.width = Math.max(0, pageW) + 'px';
  contentPaneEl.style.height = Math.max(0, divH - controlbarH) + 'px';

  // set thumb-elements's height
  var thumbEl = document.getElementById('thumb-elements');
  thumbEl.style.height = Math.max(0, divH - controlbarH) + 'px';
};

/**
 * Returns best fit width size for display in page-pane
 */
happyPDF.getPageFit = function () {
  var viewportSize = happyPDF.getViewportSize(),
    thumbPaneEl = document.getElementById('thumb-pane'),
    thumbW = thumbPaneEl.offsetWidth,
    pagefit = (viewportSize.width - thumbW) * .8;

  return pagefit;
};

// display loading dialog box
happyPDF.showDialogBox = function (msg) {
  var offsetLeft = $(document).width() / 3;
  $('#pdf-loading .ui-widget').css('left', offsetLeft);
  $('#pdf-loading .ui-widget-shadow').css('left', offsetLeft);

  // set msg
  if (msg) $('#pdf-loading .ui-widget div').text(msg);
  else $('#pdf-loading .ui-widget div').text('one moment loading...');

  $('#pdf-loading').slideDown();
}

// hide loading dialog box
happyPDF.hideDialogBox = function () {
  $('#pdf-loading').hide();
};

/**
 * Get current scrollbar position of content pane
 */
happyPDF.getScrollbarPosition = function () {
  var top = document.getElementById("content-pane").scrollTop,
    height = document.getElementById("content-pane").scrollHeight;

  // Scroll position interms of percentage
  return ((top / height) * 100);
};

/**
 * Set scrollbar position
 */
happyPDF.setScrollbarPosition = function (position) {
  var height = 0,
    new_position = 0;

  height = document.getElementById("content-pane").scrollHeight;

  // Scroll position interms of percentage
  new_position = parseInt((position / 100) * height, 10);

  // Scroll to position with ease in animate
  $('#content-pane').scrollTo(new_position + 'px', 100);
};

/**
 * Delay load of image which are out of viewport
 */
happyPDF.lazyLoad = function () {
  $("#content-pane img.lazy").lazyload({
    effect: "fadeIn",
    event: "scrollstop",
    container: $("#content-pane"),
    failure_limit: 10
  });
};

/**
 * Change src of thumbnail image tags
 */
happyPDF.changeZoomImage = function (zoom) {
  var scrollbarPosition = happyPDF.getScrollbarPosition(),
    totalPage = happyPDF.totalPage.get(),
    width = happyPDF.dataModel['resolution'][zoom] + 'px',
    img = '',
    page = '',
    largeImg = '',
    smallImg = '',
    doc = happyPDF.getDoc();

  for (var i = 0; i < totalPage; i = i + 1) {
    img = happyPDF.dataModel['images'][i][zoom];
    page = $('#page-' + i);
    page.css('width', width);

    largeImg = page.find('img:first');
    largeImg.css('display', 'none');
    largeImg.remove(); // destory all the bindings made by previous lazy loading

    smallImg = page.find('img:last');
    smallImg.css('display', 'none');
    smallImg.css('width', width);
    smallImg.css('display', 'block');

    // create new image
    var newImg = doc.createElement('img');
    newImg.setAttribute('src', smallImg.attr('src'));
    newImg.setAttribute('data-original', img.src);
    newImg.setAttribute('class', "page-image lazy");
    newImg.style.width = width;
    newImg.style.display = "block";

    smallImg.before(newImg);
    smallImg.css('display', 'none');
  }

  // Delay load of image which are out of viewport
  happyPDF.lazyLoad();

  // scrollbar back to previous position
  happyPDF.setScrollbarPosition(scrollbarPosition);
};


/**
 * Scroll to particular page
 */
happyPDF.scrollToPage = function (id, oldId) {
  console.assert(id >= 0);

  $('#content-pane').scrollTo($('#page-' + id), 300);

  var thumb = $('#thumb-' + id);

  // add highlight to thumb-image
  var emphasize = 'thumb-image-emphasized';
  thumb.addClass(emphasize);
  thumb.find('img:first').addClass(emphasize);

  // clean up old thumb highlight
  if (oldId >= 0) {
    $('#thumb-' + oldId).removeClass(emphasize);
    $('#thumb-' + oldId).find('img:first').removeClass(emphasize);
  }
};

/**
 * Rotate document to left. This is done by using css3 and classname.
 */
happyPDF.rotateLeft = function (id) {
  var angle = 0;
  var el = $(id);

  switch (true) {
    case el.hasClass('rotate-90'):
      el.removeClass('rotate-90');
      angle = 0;
      break;

    case el.hasClass('rotate-180'):
      el.removeClass('rotate-180').addClass('rotate-90');
      angle = 90;
      break;

    case el.hasClass('rotate-270'):
      el.removeClass('rotate-270').addClass('rotate-180');
      angle = 180;
      break;

    default:
      el.addClass('rotate-270');
      angle = 270;
  }

  return angle;
};

/**
 * Rotate document to right by changing it's class name
 * @param {String} id
 * @return {Integer}
 */
happyPDF.rotateRight = function (id) {
  var angle = 0;
  var el = $(id);

  switch (true) {
    case el.hasClass('rotate-90'):
      el.removeClass('rotate-90').addClass('rotate-180');
      angle = 180;
      break;

    case el.hasClass('rotate-180'):
      el.removeClass('rotate-180').addClass('rotate-270');
      angle = 270;
      break;

    case el.hasClass('rotate-270'):
      el.removeClass('rotate-270');
      angle = 0;
      break;

    default:
      el.addClass('rotate-90');
      angle = 90;
  }

  return angle;
};

//-------------------- [end] Page helper

/**
 * Initialize control bars
 */
happyPDF.initControlbarHandler = function () {
  // handle click on thumbnail
  $('#thumb-elements .thumb-element').on('click', function (event) {
    var id = $(this).children().first().attr('id').split('-')[1];
    id = parseInt(id, 10);

    console.assert(id >= 0);

    happyPDF.currentPage.set(id);
    event.preventDefault();
  });

  // goto previous page
  $('#prevPageBtn').on('click', function (event) {
    happyPDF.currentPage.set(happyPDF.currentPage.get() - 1);
    event.preventDefault();
    event.stopPropagation();
  });

  // goto next page
  $('#nextPageBtn').click(function (event) {
    happyPDF.currentPage.set(happyPDF.currentPage.get() + 1);
    event.preventDefault();
    event.stopPropagation();
  });

  // zoom out
  $('#zoomOutBtn').click(function (event) {
    happyPDF.currentZoom.set(happyPDF.currentZoom.get() - 1);
    event.preventDefault();
    event.stopPropagation();
  });

  // zoom in
  $('#zoomInBtn').click(function (event) {
    happyPDF.currentZoom.set(happyPDF.currentZoom.get() + 1);
    event.preventDefault();
    event.stopPropagation();
  });

  // rotate left
  $('#rotateLeftBtn').click(function (event) {
    var id = happyPDF.currentPage.get();
    happyPDF.rotateLeft('#page-' + id);
    happyPDF.rotateLeft('#thumb-' + id);

    event.preventDefault();
    event.stopPropagation();
  });

  // rotate right
  $('#rotateRightBtn').click(function (event) {
    var id = happyPDF.currentPage.get();
    happyPDF.rotateRight('#page-' + id);
    happyPDF.rotateRight('#thumb-' + id);

    event.preventDefault();
    event.stopPropagation();
  });
};

// Property variables
happyPDF.currentPage  = happyPDF.frp.observable(null),
happyPDF.totalPage    = happyPDF.frp.observable(null),   // dataModel['total-page']
happyPDF.currentZoom  = happyPDF.frp.observable(null), // 800px zoom level
happyPDF.maxZoom      = happyPDF.frp.observable(null),     // dataModel['resolution'].length
happyPDF.minZoom      = happyPDF.frp.observable(null)

happyPDF.dataModel = {
  'guid': '',
  'thumb-element-html': '',
  'page-pane-html': '',
  'images': [
    [{
      'src': '',
      'W': '',
      'h': ''
    }]
  ],
  'resolution': [],
  'total-page': 0,

  'rectData': '',
  'zoomRectData': [],
  'highlightedData': []
};

happyPDF.setDataModel = function (data) {
  if (data['thumb-element-html'] === undefined && data['page-pane-html'] === undefined && data['imges'] === undefined && data['resolution'] === undefined && data['total-page'] === undefined) {
    happyPDF.showDialogBox('Error setting data model');
    return false;
  }

  happyPDF.dataModel['guid'] = data['guid'];
  happyPDF.dataModel['thumb-element-html'] = data['thumb-element-html'];
  happyPDF.dataModel['page-pane-html'] = data['page-pane-html'];
  happyPDF.dataModel['images'] = data['images'];
  happyPDF.dataModel['resolution'] = data['resolution'];
  happyPDF.dataModel['total-page'] = parseInt(data['total-page'], 10);

  // setup variables
  happyPDF.minZoom.declare(0);
  happyPDF.maxZoom.declare(happyPDF.dataModel['resolution'].length);
  happyPDF.totalPage.declare(happyPDF.dataModel['total-page']);
  happyPDF.currentPage.declare(0);
  happyPDF.currentZoom.declare(happyPDF.calcDefaultZoom());

  return true;
};

// Calculate best zoom according to current window size
happyPDF.calcDefaultZoom = function () {
  var pageFit = happyPDF.getPageFit(),
    resolutions = happyPDF.dataModel['resolution'],
    len = resolutions.length;

  for (var i = 0; i < len; i++) {
    if (pageFit >= resolutions[i]) {
      if (pageFit < resolutions[i + 1]) {
        return i;
      }
    }
  }

  // default zoom
  return 0;
};

happyPDF.setupPageDisplay = function () {
  // render panes
  $('#thumb-pane').html(happyPDF.dataModel['thumb-element-html']);
  $('#page-pane').html(happyPDF.dataModel['page-pane-html']);

  // listen to window resize handler
  happyPDF.resizeWindowHandler();
  window.onresize = happyPDF.initialResize;

  // handle event loading stuffs.
  happyPDF.initControlbarHandler();

  // Delay load of image which are out of viewport
  happyPDF.lazyLoad();

  happyPDF.scrollToPage(0, null);
};

// current page
happyPDF.frp.setListener(happyPDF.currentPage, function (value, oldValue) {
  var totalPage = happyPDF.totalPage.get();

  if (totalPage === null) {
    return true;
  }

  if (value >= 0 && value < totalPage) {
    happyPDF.scrollToPage(value, oldValue);
    return true;

  } else {
    return false;
  }
});

// change thumbnail image source according to zoom level
happyPDF.frp.setListener(happyPDF.currentZoom, function (value, oldValue) {
  var maxZoom = happyPDF.maxZoom.get(),
    minZoom = happyPDF.minZoom.get();

  if (maxZoom === null && minZoom === null) {
    return true;
  }

  if (value >= minZoom && value < maxZoom) {
    happyPDF.changeZoomImage(value);

    // rest text selection
    happyPDF.highlightSelectionRemove();
    happyPDF.zoomPDFTextBlock(value);
    return true;
  } else {
    return false;
  }
});

happyPDF.highlightSelectionRemove = function () {
  $('.selection-highlight').remove();
};

happyPDF.zoomPDFTextBlock = function (zoom) {
  function debugPDFTextBlock(i, l, t, w, h) {
    var elt = '<div class="selection-highlight" style="position: absolute; left: ' + l + 'px; top: ' + t + 'px; width: ' + w + 'px; height: ' + h + 'px; background-color: orange; z-index: 99; opacity: 0.5;"></div>';
    $('#page-' + i).prepend(elt);
  };

  function applyScale() {
    var offsetTop = 24,
      offsetLeft = 8;

    var pageWidth = parseInt(happyPDF.dataModel['resolution'][zoom], 10); // 800px

    var scaleData = {
      page: []
    };

    happyPDF.dataModel.rectData.page.forEach(function (page, i) {
      var scale = pageWidth / rectData.page[i].w;
      scale = parseFloat(scale).toFixed(2);

      var data = [];
      var content = rectData.page[i].content;

      for (var j in content) {
        var l = Math.floor(content[j].l * scale) + offsetLeft;
        var t = Math.floor(content[j].t * scale) + offsetTop;
        var w = Math.floor(content[j].w * scale);
        var h = Math.floor(content[j].h * scale);

        data.push({
          'l': l,
          't': t,
          'w': w,
          'h': h,
          'text': content[j].text
        });

        //~ debugPDFTextBlock(i, l, t, w, h);
      }

      scaleData.page.push(data);
    });

    return scaleData;
  }

  // zoom PDF text block
  var rectData = happyPDF.dataModel.rectData;
  if (typeof rectData === "object") {
    happyPDF.dataModel.zoomRectData = applyScale();
  }
};

// rectangular collision detection functions
happyPDF.rect = {
  // check if coordinate value is within range
  "_valueInRange": function (value, min, max) {
    return (value <= max) && (value >= min);
  },

  // check if rectangles overlaps each other
  "isOverlap": function (A, B) {
    var xOverlap = happyPDF.rect._valueInRange(A.l, B.l, B.l + B.w) || happyPDF.rect._valueInRange(B.l, A.l, A.l + A.w);
    var yOverlap = happyPDF.rect._valueInRange(A.t, B.t, B.t + B.h) || happyPDF.rect._valueInRange(B.t, A.t, A.t + A.h);
    return xOverlap && yOverlap;
  }
};

// draw a rubber band around image
happyPDF.rubberband = function (evt) {
  var elt = $('#rubberband'),
    left = evt.pageX + this.scrollLeft - this.offsetLeft,
    top = evt.pageY + this.scrollTop - this.offsetTop,
    width = 0,
    height = 0,
    self = this;

  var stretch = function (evt) {
    var debugRubberband = function () {
      $('#rubberband-4').css({
        'width': w + 'px',
        'height': h + 'px',
        'left': (l - 150) + 'px',
        'top': (t - $('#page-0')[0].offsetHeight - $('#page-1')[0].offsetHeight - $('#page-2')[0].offsetHeight - $('#page-3')[0].offsetHeight) + 'px'
      });

      $('#rubberband-3').css({
        'width': w + 'px',
        'height': h + 'px',
        'left': (l - 150) + 'px',
        'top': (t - $('#page-0')[0].offsetHeight - $('#page-1')[0].offsetHeight - $('#page-2')[0].offsetHeight) + 'px'
      });

      $('#rubberband-2').css({
        'width': w + 'px',
        'height': h + 'px',
        'left': (l - 150) + 'px',
        'top': (t - $('#page-0')[0].offsetHeight - $('#page-1')[0].offsetHeight) + 'px'
      });
    };

    var nleft = evt.pageX + self.scrollLeft - self.offsetLeft,
      ntop = evt.pageY + self.scrollTop - self.offsetTop,
      w = Math.abs(nleft - left),
      h = Math.abs(ntop - top),
      l = (nleft - left < 0 ? nleft : left),
      t = (ntop - top < 0 ? ntop : top);

    elt.css({
      'width': w + 'px',
      'height': h + 'px',
      'left': l + 'px',
      'top': t + 'px'
    });

    //debugRubberband();

    // add highlight to selected words
    setTimeout(function () {
      happyPDF.highlightSelection({
        'w': w,
        'h': h,
        'l': l,
        't': t
      });
    }, 100);

    return false;
  };

  var stop = function (evt) {
    elt.removeClass('rubberband').css('display', 'none');
    $(self).off('mousemove', stretch);
    return false;
  };

  var start = function (evt) {
    elt.addClass('rubberband');
    elt.css({
      'width': width + 'px',
      'height': height + 'px',
      'left': left + 'px',
      'top': top + 'px',
      'display': 'block'
    });

    // move and stretch rubber band
    $(self).on('mousemove', stretch);
    $(self).on('mouseup', stop);

    // prevent dragging of background image
    evt.preventDefault();
    return false;
  };

  start(evt);
};

happyPDF.highlightSelection = function (rectRubberband) {
  var drawInnerRectangle_debug = function (rectRubberbandAdjust, page_no) {
    $('#rubberband-' + page_no).css({
      'width': rectRubberbandAdjust.w + 'px',
      'height': rectRubberbandAdjust.h + 'px',
      'left': rectRubberbandAdjust.l + 'px',
      'top': rectRubberbandAdjust.t + 'px'
    });
  };

  var getRubberbandSelectedPages = function () {
    var pane = $('#page-pane').children();
    var pages = [];
    for (var i = 0, length = pane.length; i < length; i++) {
      var rectPage = {
        l: pane[i].offsetLeft,
        t: pane[i].offsetTop,
        w: pane[i].offsetWidth,
        h: pane[i].offsetHeight
      };

      if (happyPDF.rect.isOverlap(rectPage, rectRubberband)) {
        pages.push(i);
      }
    }
    return pages;
  };

  var calcTop = function (page_no, t) {
    var offsetHeight = 0,
      currentPage = page_no;

    if (page_no <= 0) return t;


    //var extraDiff = Math.abs( $('#page-' + currentPage)[0].offsetHeight - $('#page-' + (page_no - 1))[0].offsetHeight );

    do {
      var heightDiff = Math.abs($('#page-' + currentPage)[0].offsetHeight - $('#page-' + (page_no - 1))[0].offsetHeight);
      var topDiff = Math.abs($('#page-' + currentPage)[0].offsetTop - $('#page-' + (page_no - 1))[0].offsetTop);

      // pages not at same level, need to add height
      // fix for FF bug: offsetTop calculation, Math.abs(topDiff - heightDiff) > 1
      if (topDiff != heightDiff && Math.abs(topDiff - heightDiff) > 1) {
        currentPage = page_no - 1;
        offsetHeight += $('#page-' + currentPage)[0].offsetHeight;
      }

      //console.log("", heightDiff);

      page_no--;
    } while (page_no >= 1);

    return (t - offsetHeight);
  };

  var getSelectedTextBlock = function (page_no) {
    var data = [];
    var rectRubberbandAdjust = {
      'w': rectRubberband.w,
      'h': rectRubberband.h,
      'l': rectRubberband.l - $('#page-' + page_no)[0].offsetLeft,
      't': calcTop(page_no, rectRubberband.t)
    };

    var content = happyPDF.dataModel.zoomRectData.page[page_no];

    for (var i in content) {
      var rectWord = {
        l: content[i].l,
        t: content[i].t,
        w: content[i].w,
        h: content[i].h
      };

      if (happyPDF.rect.isOverlap(rectWord, rectRubberbandAdjust)) {
        data.push(content[i]);
      }
    }

    return data;
  };

  // clear previous selection
  happyPDF.highlightSelectionRemove();

  var data = [];

  // loop through pages to highlight selected word that are within the boundry of rubberband
  var pages = getRubberbandSelectedPages();

  pages.forEach(function (page_no) {
    var txtBlock = getSelectedTextBlock(page_no);

    if (txtBlock.length > 0) {
      data = $.merge(data, txtBlock)
    }

    var elt = '';
    for (var i in txtBlock) {
      elt += '<div class="selection-highlight" style="position: absolute; left: ' + txtBlock[i].l + 'px; top: ' + txtBlock[i].t + 'px; width: ' + txtBlock[i].w + 'px; height: ' + txtBlock[i].h + 'px; background-color: orange; opacity: 0.5;"></div>';
    }
    $('#page-' + page_no).prepend(elt);
  });

  // set data up for clipboard oncopy event
  happyPDF.dataModel.highlightedData = data;
};

happyPDF.clipboardCopyHandler = function () {
  $(document).on('keydown', function (evt) {
    if (evt.ctrlKey == true && evt.keyCode == 67) {

      // give user high-lighted text
      var txt = [];
      happyPDF.dataModel.highlightedData.forEach(function (data) {
        txt.push(data['text']);
      });

      happyPDF.clipboard.copyText(txt.join(' '));
    }
  });
};

// apply xslt over xml and get text block coordinates object
happyPDF.initPDFTextBlock = function (guid) {
  var loadXSLT = function () {
    var xsltUrl = happyPDF.baseURI() + "public/xml/json.xsl";

    $.get(xsltUrl, function (response) {
      xsltDoc = response;

    }, 'xml').success(function () {
      loadXML();

    }).error(function () {
      console.log('error loading file!');
    });
  };

  cleanRectData = function (rectData) {
    for (var i = 0, len = rectData.page.length; i < len; i++) {
      var content = rectData.page[i].content;

      // remove empty page
      if (!rectData.page[i].number) {
        delete rectData.page[i];
      }

      // remove empty content
      for (var j in content) {
        if (!content[j].l) {
          delete rectData.page[i].content[j];
        }
      }
    }

    return rectData
  };

  var loadXML = function () {
    
    if (guid) {
      xmlUrl = happyPDF.baseURI() + "pdf/to_xml/" + guid;
      
      $.get(xmlUrl, function (response) {
        xmlDoc = response;

        // initialize text block
        setup();

      }, 'xml').success(function () {
        //console.log('successfully loaded xml');

      }).error(function () {
        console.log('error loading file!');
      });
    }
    
    //~ var xmlUrl = happyPDF.baseURI() + "public/xml/sdl.xml";
    //~ $.get(xmlUrl, function (response) {
      //~ xmlDoc = response;
//~ 
      //~ // setup
      //~ setup();
//~ 
    //~ }, 'xml').success(function () {
      //~ //console.log('successfully loaded xml');
//~ 
    //~ }).error(function () {
      //~ console.log('error loading file!');
    //~ });
  };

  var setup = function () {
    // apply xslt and get pdf text block object from xml
    var result = happyPDF.applyXSLT(xmlDoc, xsltDoc);
    var rectData = JSON.parse(result.textContent);

    rectData = cleanRectData(rectData);
    happyPDF.dataModel['rectData'] = rectData;

    // resize PDF text block to match current zoom level
    var zoom = happyPDF.currentZoom.get() || 0;
    happyPDF.zoomPDFTextBlock(zoom);

    // set rubberband
    $('#content-pane').on('mousedown', happyPDF.rubberband);

    // handle clipboard copy
    happyPDF.clipboardCopyHandler();
  };

  var xmlDoc = null,
    xsltDoc = null;

  loadXSLT();
};

// toolbar event handling
happyPDF.initPDF = function () {
  var url_pdf = $('#url-pdf').val(),
    doc_url = happyPDF.getWin().location.search;

  if (url_pdf.trim() === '') {
    happyPDF.showDialogBox('Error loading pdf file');
    return false;
  }

  happyPDF.showDialogBox();

  url_pdf = url_pdf + '?pagefit=' + happyPDF.getPageFit() + '&' + 'url=' + doc_url.substr(5);

  $.get(url_pdf, function (response) {
    if (response.err === undefined) {
      // load dataModel
      happyPDF.setDataModel(response);
      
      // display page
      happyPDF.setupPageDisplay();
      
      // hide loading dialog box
      happyPDF.hideDialogBox();
      
      // initialize PDF text block for selection
      happyPDF.initPDFTextBlock(happyPDF.dataModel.guid);

    } else {
      happyPDF.showDialogBox('Error loading file!');
    }
  }, 'json').success(function () {
    //console.log('successfully loaded xml');
  }).error(function () {
    happyPDF.showDialogBox('Error loading file!');
  });
};

// Application initialize
$(document).ready(function () {
  happyPDF.initPDF();  
});
