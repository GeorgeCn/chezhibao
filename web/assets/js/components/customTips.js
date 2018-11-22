//customTips
var customTips = {
  init:function($els){
    this.$original = $els;
    this.timer = null;
    this.$customTips = null;
    this.duration = 50;
    this.handle();
  },
  handle:function(){
    var self = this;
    this.$original.hover(
      function(){
        var tips = $.trim($(this).text());
        clearTimeout(self.timer);
        self.show($(this),tips);
      },
      function(){
        self.timer = setTimeout(function(){
          self.hide();
        },self.duration);
      }
    )
  },
  tipsHover:function($customTips){
    var self = this;
    $customTips.hover(
      function(){
        clearTimeout(self.timer);
      },
      function(){
        self.timer = setTimeout(function(){
          self.hide();
        },self.duration);
      }
    )
  },
  createTips:function(tips){
    var tipsHTML = "";
    var reg = /\s{2,}/g;
    tips = tips.replace(reg,"<br>");
    if(this.$customTips && this.$customTips.length){
      this.$customTips.find(".custom-tips-content").html(tips);
    }else{
      tipsHTML += '<div class="custom-tips" id="customTips">'
                +   '<span class="custom-tips-arrow"></span>'
                +   '<div class="custom-tips-content">'+tips+'</div>'
                + '</div>';
      this.$customTips = $(tipsHTML);
      $("body").append(this.$customTips);
      this.tipsHover(this.$customTips);
    }
  },
  position:function($el){
    var left,top;
    var offset = $el.offset();
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var tipsWidth = this.$customTips.outerWidth();
    var tipHeight = this.$customTips.outerHeight();
    var elWidth = $el.outerWidth();
    var elHeight = $el.outerHeight();
    var safeSpacing = 50;

    if(offset.left+tipsWidth+safeSpacing>windowWidth){
      left = offset.left-(offset.left+tipsWidth+safeSpacing-windowWidth);
      this.$customTips.addClass('tips-right').removeClass('tips-left');
    }else{
      left = offset.left;
      this.$customTips.addClass('tips-left').removeClass('tips-right');
    }

    if(offset.top+elHeight+tipHeight>windowHeight){
      top = offset.top-tipHeight;
      this.$customTips.addClass('tips-top').removeClass('tips-bottom');
    }else{
      top = offset.top+elHeight;
      this.$customTips.addClass('tips-bottom').removeClass('tips-top');
    }

    this.$customTips.css({
      "left":left,
      "top":top,
      "opacity":1
    });
  },
  show:function($el,tips){
    var width = $el.innerWidth();
    if(tips==""){
      return;
    }
    this.createTips(tips);
    this.$customTips.css({
      "display":"block",
      "width":width
    })
    this.position($el);
  },
  hide:function(){
    this.$customTips && this.$customTips.css({
      "opacity":0,
      "display":"none"
    });
  }
}