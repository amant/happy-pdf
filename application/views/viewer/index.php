<div id="pdf-loading" style="display:none">
  <!-- ui-dialog -->
  <div class="ui-overlay">
    <div class="ui-widget-overlay"></div>
    <div style="width: 302px; height: 100px; position: absolute; left: 50px; top: 30px; z-index: 99;" class="ui-widget-shadow ui-corner-all"></div>    
  </div>
  <div class="ui-widget ui-widget-content ui-corner-all ui-state-highlight" style="position: absolute; width: 280px; height: 78px;left: 50px; top: 30px; padding: 10px; z-index: 100;">
      <div style="font-size: 16px; text-align: center;">one moment loading...</div>
  </div>
</div>

<div id="view" class="view">
    <div id="controlbar">
        <div id="button-elements">
            <div class="toolbar-button"><a href="#" id='prevPageBtn'>Prev Page (&lt;)</a></div>
            <div class="toolbar-button"><a href="#" id='nextPageBtn'>Next Page(&gt;)</a></div>
            <div class="toolbar-button"><a href="#" id='zoomOutBtn'>Zoom Out (-)</a></div>
            <div class="toolbar-button"><a href="#" id='zoomInBtn'>Zoom In (+)</a></div>
            <div class="toolbar-button"><a href="#" id='rotateLeftBtn'>Rotate Left</a></div>
            <div class="toolbar-button"><a href="#" id='rotateRightBtn'>Rotate Right</a></div>
        </div>
    </div> <!-- end controlbar -->

    <div id="thumb-pane"></div> <!-- end thumb-pane -->

    <div id="content-pane" style="">
        <div id="page-pane"></div>
        <div class="" id="rubberband"></div>
    </div> <!-- end content-panel -->
</div>


<?php /*<div id="gview">
    <div id="view" class="view">
        <div id="controlbar" class="">
            <?php echo $this->template->block('controlbar', '/viewer/_controlbar'); ?>
        </div>
        <div id="thumb-pane" class="thumb-pane" style="top: 122px; left: 0px; width: 236px; height: 97px;">
            <?php echo $this->template->block('controlbar', '/viewer/_controlbar'); ?>
        </div>
        <div id="content-pane" class="gview-scrollbar" style="left: 236px; top: 122px; width: 1130px; height: 97px;">
            <?php echo $this->template->block('controlbar', '/viewer/_controlbar'); ?>
        </div>
    </div>
</div>*/ ?>
