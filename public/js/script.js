/**
 * Singleton Object
 */
var happyPDF = {    
    /**
     * Returns width/height of document body
     */
    getViewportSize: function () {
        var el = window.document.body;
        return {width: el.clientWidth, height: el.clientHeight};
    },

    /**
     * calculate the width/height of thumb-pane, content-pane and controlbar
     * 
     * TODO: remove default width and height from thumb-pane, content-pane and thumb-elements
     */
    initialResize: function () {
        var viewEl = document.getElementById('view');
        var viewportSize = happyPDF.getViewportSize();
        var left = viewEl.offsetLeft;
        var top = viewEl.offsetTop;
        var divW = viewportSize.width;
        var divH = viewportSize.height;

        var thumbPaneEl = document.getElementById('thumb-pane');
        var thumbW = thumbPaneEl.offsetWidth;
        var thumbL = divW - thumbW;
        var pageW = thumbL - left;

        // set content-pane's style:left
        var contentPaneEl = document.getElementById('content-pane');
        contentPaneEl.style.left = left + thumbW + 'px';

        var controlbarH = 0;
        var controlbarEl = document.getElementById('controlbar');
        controlbarH = controlbarEl.offsetHeight;

        // set controlbar's width and height
        contentPaneEl.style.width = Math.max(0, pageW) + 'px';
        contentPaneEl.style.height = Math.max(0, divH - controlbarH) + 'px';

        // set thumb-elements's height
        var thumbEl = document.getElementById('thumb-elements');
        thumbEl.style.height = Math.max(0, divH - controlbarH) + 'px';
    },

    /**
     * Returns best fit width size for display in page-pane
     */
    getPageFit: function() {
        var viewportSize = happyPDF.getViewportSize();

        var thumbPaneEl = document.getElementById('thumb-pane');
        var thumbW = thumbPaneEl.offsetWidth;

        var pagefit = (viewportSize.width - thumbW ) * .8;

        return pagefit;
    },
    
    /**
     * Rotate document to left. This is done by using css3 and classname.
     * 
     */
    rotateLeft: function(id) {
        var angle = 0;
        var el = $('#' + id);

        switch(true) {
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
    },
    
    /**
     * Rotate document to right by changing it's class name
     * @param {String} id
     * @return {Integer}
     * 
     */    
    rotateRight: function(id) {
        var angle = 0;
        var el = $('#' + id);

        switch(true) {

            case el.hasClass('rotate-90') :
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
    }
};



// Structure of Data Model which stores the structure of document
happyPDF.dataModel = {
    "thumb-element-html": "",
    "page-pane-html": "",
    "images":[[{"src":"", "W": "", "h": ""}]],
    "resolution":[],
    "total-page": 0    
};

happyPDF.setDataModel = function (data) {
  
  if (data['thumb-element-html'] === undefined &&
      data['page-pane-html'] === undefined && 
      data['imges'] === undefined &&
      data['resolution'] === undefined &&
      data['total-page'] === undefined) {      
      alert('Error setting data model');
      throw "Error setting data model";      
      
      return false;
  }      

  happyPDF.dataModel['thumb-element-html'] = data['thumb-element-html'];
  happyPDF.dataModel['page-pane-html'] = data['page-pane-html'];
  happyPDF.dataModel['images'] = data['images'];
  happyPDF.dataModel['resolution'] = data['resolution'];
  happyPDF.dataModel['total-page'] = data['total-page'];  
  
  return true;
};

happyPDF._default_page = 0;
happyPDF._total_page = 5;	// dataModel['total-page']

happyPDF._default_zoom = 5;
happyPDF._max_zoom = 7; // dataModel['resolution'].length
happyPDF._min_zoom = 1;

happyPDF.setZoom = function(zoom) {
    happyPDF._default_zoom = parseInt(zoom, 10);
};

happyPDF.getZoom = function() {
    return happyPDF._default_zoom;
};

happyPDF.setMaxZoom = function(max) {
    happyPDF._max_zoom = parseInt(max, 10);
};

happyPDF.getMaxZoom = function() {
    return happyPDF._max_zoom;
};

happyPDF.setMinZoom = function(min) {
    happyPDF._min_zoom = parseInt(min, 10);
};

happyPDF.getMinZoom = function() {
    return happyPDF._min_zoom;
};

happyPDF.setDefaultPage = function(page) {
    happyPDF._default_page = parseInt(page, 10);
};

happyPDF.getDefaultPage = function() {
    return happyPDF._default_page;
};

happyPDF.setTotalPage = function(total) {
    happyPDF._total_page = parseInt(total, 10);
};

happyPDF.getTotalPage = function() {
    return happyPDF._total_page;
};

happyPDF.scrollToPage = function(el) {
    // old page
    var old_page = happyPDF.getDefaultPage(page);

    var thumb = el.children().first();

    // current page
    var page = parseInt(thumb.attr('id').split('-')[1], 10);    

    // set current page as default page
    happyPDF.setDefaultPage(page);

    var emphasize = 'thumb-image-emphasized';

    // add highlight to thumb-image
    el.addClass(emphasize);

    thumb.find('img:first').addClass(emphasize);

    // scroll page
    $('#content-pane').scrollTo($('#page-' + page), 300);

    // clean up old thumb highlight
    if(old_page !== page) {
      $('#thumb-' + old_page).parent().removeClass(emphasize);
      $('#thumb-' + old_page).find('img:first').removeClass(emphasize);
      $('#thumb-' + old_page).find('.highlifht-pane:first').remove();
    }    
};


// Get current scrollbar position of content pane
happyPDF.getScrollbarPosition = function() {

    var top = document.getElementById("content-pane").scrollTop;
    var height = document.getElementById("content-pane").scrollHeight;

    // Scroll position interms of percentage
    return ((top / height) * 100);
};

happyPDF.setScrollbarPosition = function(position) {
    var height = 0,
    new_position = 0;

    height = document.getElementById("content-pane").scrollHeight;

    // Scroll position interms of percentage
    new_position = parseInt((position / 100) * height, 10);

    // Scroll to position with ease in animate
    $('#content-pane').scrollTo(new_position + 'px', 100);

    // Scroll to position without-animation
    //document.getElementById("content-pane").scrollTop = new_position;
},

// Delay load of image which are out of viewport
happyPDF.lazyLoad = function() {  
    $("#content-pane img.lazy").lazyload({
        effect : "fadeIn",
        event: "scrollstop",
        container: $("#content-pane"),
        failure_limit : 10
    });
},

// Change src of thumbnail image tags
happyPDF.changeZoomImage = function (zoom_level) {
    // Get current scrollbar position
    var current_scrollbar = happyPDF.getScrollbarPosition();
    var total_page = happyPDF.getTotalPage();

    var zoom = zoom_level - 1;
    var resolution = happyPDF.dataModel['resolution'][zoom] + 'px';
    var img = '';

    for (var page_no = 0; page_no < total_page; page_no = page_no + 1) {
        img = happyPDF.dataModel['images'][page_no][zoom];
        page = $('#page-' + page_no);
        page.css('width', resolution);

        var largeImg = page.find('img:first');
        var smallImg = page.find('img:last');

        largeImg.css('display', 'none');
        smallImg.css('display', 'none');
        smallImg.css('width', resolution);
        smallImg.css('height', img.h);
        smallImg.css('display', 'block');

        // destory all the bindings made by lazy loading
        largeImg.remove();

        // create new image
        var newImg = document.createElement('img');
        newImg.setAttribute('src', smallImg.attr('src'));
        newImg.setAttribute('data-original', img.src);
        newImg.setAttribute('class', "page-image lazy");
        newImg.style.width = resolution;
        newImg.style.height = img.h;
        newImg.style.display = "block";

        smallImg.before(newImg);
        smallImg.css('display', 'none');
    }

    // Delay load of image which are out of viewport
    happyPDF.lazyLoad();    

    // Set new scrollbar position
    happyPDF.setScrollbarPosition(current_scrollbar);
};

happyPDF.initialControlbar = function() {
    // thumbnail click
    $('#thumb-elements .thumb-element').click(function (event) {
        event.preventDefault();
        happyPDF.scrollToPage($(this));
    });


    // toolbar event handlings
    $('#prevPageBtn').unbind();
    $('#prevPageBtn').click(function (event){
        event.preventDefault();
        event.stopPropagation();

        var current_page = happyPDF.getDefaultPage();
        var total_page = happyPDF.getTotalPage();
        var prev_page = current_page - 1;

        if (prev_page < 0) {
            prev_page = total_page - 1;
        }

        var el = $('#thumb-' + prev_page).parent();
        happyPDF.scrollToPage(el);
    });

    $('#nextPageBtn').unbind();
    $('#nextPageBtn').click(function (event){
        event.preventDefault();
        event.stopPropagation();

        var current_page = happyPDF.getDefaultPage();
        var total_page = happyPDF.getTotalPage();
        var next_page = current_page + 1;

        if (next_page >= total_page) {
            next_page = 0;
        }

        var el = $('#thumb-' + next_page).parent();
        happyPDF.scrollToPage(el);
    });

    $('#zoomOutBtn').unbind();
    $('#zoomOutBtn').click(function (event){
        event.preventDefault();
        event.stopPropagation();

        // Store last zoom level state
        var current_zoom = happyPDF.getZoom();
        var new_zoom = current_zoom - 1;
        var min_zoom = happyPDF.getMinZoom();

        if(new_zoom >= min_zoom) {
            happyPDF.setZoom(new_zoom);

            // change thumbnail image source according to zoom level
            happyPDF.changeZoomImage(new_zoom);

        } else {
            console.log('Min zoom reached!');
        }

    });

    $('#zoomInBtn').unbind();
    $('#zoomInBtn').click(function (event){
        event.preventDefault();
        event.stopPropagation();

        // Store last zoom level state
        var current_zoom = happyPDF.getZoom();
        var new_zoom = current_zoom + 1;
        var max_zoom = happyPDF.getMaxZoom();

        if(new_zoom <= max_zoom) {

            happyPDF.setZoom(new_zoom);
            //console.log(new_zoom);

            // change thumbnail image source according to zoom level
            happyPDF.changeZoomImage(new_zoom);

        } else {
            console.log('Max zoom reached!');
        }

    });

    $('#rotateLeftBtn').unbind();
    $('#rotateLeftBtn').click(function (event){
        event.preventDefault();
        event.stopPropagation();

        var page = happyPDF.getDefaultPage();

        happyPDF.rotateLeft('page-' + page);
        happyPDF.rotateLeft('thumb-' + page);
    });

    $('#rotateRightBtn').unbind();
    $('#rotateRightBtn').click(function (event){
        event.preventDefault();
        event.stopPropagation();

        var page = happyPDF.getDefaultPage();

        happyPDF.rotateRight('page-' + page);
        happyPDF.rotateRight('thumb-' + page);
    });  
};


$(document).ready(function() {
  // loading... dialog box
  $('#pdf-loading .ui-widget').css('left', $(document).width() / 3);
  $('#pdf-loading .ui-widget-shadow').css('left', $(document).width() / 3);
  
});

// TODO: all the unbinding() should be done at document.unload event
// toolbar event handling
$(document).ready(function () {
    var url_pdf = $('#url-pdf').val();
    
    var doc_url = window.location.search;
    
    var pdf_loading_text = $('#pdf-loading ui-widget div').val();
    
    //var doc_url = encodeURIComponent('a.pdf');
    if(doc_url === '' || doc_url === ' ') {      
      $('#pdf-loading ui-widget div').val('Error loading pdf file');      
      return false;      
    }
    
    url_pdf = url_pdf + '?pagefit=' + happyPDF.getPageFit() + '&' + 'url=' + doc_url.substr(5);
    
    $('#pdf-loading ui-widget div').val(pdf_loading_text);
    $('#pdf-loading').slideDown();
    
    $.get(url_pdf, function(response) {
      if(response.err === undefined && happyPDF.setDataModel(response) === true) {          
        
          $('#pdf-loading').hide();
        
          // set layout
          $('#page-pane').html(happyPDF.dataModel['page-pane-html']);
          $('#thumb-pane').html(happyPDF.dataModel['thumb-element-html']);

          // layout re-sizing
          happyPDF.initialResize();  
          window.onresize = happyPDF.initialResize;
                    
          happyPDF.scrollToPage($('.thumb-element:first'));
    
          // handle event loading stuffs.
          happyPDF.initialControlbar();      
          
          // Delay load of image which are out of viewport
          happyPDF.lazyLoad();
          
          // make ajax request
          // load dataModel
          // initilize singleton variable
          // attach or deligate events to resize, toolbar, thumbnails, scrollbars, etc...
          
      } else {
        console.log('Error');
      }      
    }, 'json')
    .success(function(){
      //console.log('Success');
    })
    .error(function(){
      //console.log('Error');      
    });        
});