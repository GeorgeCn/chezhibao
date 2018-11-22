$.fn.videoCt = function (options) {
    //设置默认值
    var defaults = {
        title: '',
        volume: 0,
        playSpeed: false,
        autoplay: true,
        clarity: {
            src: []
        },
        theme: 'blue',
    };
    var opts = $.extend({},defaults, options);
    var intOpts = {
        title: '',
        status: true,
        currentTime: '0.00001',
        cutover:true,
        duration: '',
        volume: '',
        claritySrc: '',
        playSpeed: 1,
    };

    // 控件结构
    var $videoCt = $(this);
    var $videoCtBox = $('<div></div>').addClass('video-player').addClass(opts.theme);
    var $videoCtControls = $('<div class="video-controls">\
                                <!--进度条-->\
                                <div class="video-seek">\
                                    <div class="seek-buffer"></div>\
                                    <div class="seek-con"></div>\
                                    <div class="seek-slider"></div>\
                                    <div class="viewBox">\
                                        <video preload="auto"></video>\
                                        <span>00:00</span>\
                                        <i></i>\
                                    </div>\
                                </div>\
                                <!--播放/暂停-->\
                                <a class="video-play" title="Play/Pause"></a>\
                                <!--计时器-->\
                                <div class="video-timer">\
                                    <span class="realTime">00:00</span>\
                                    /&nbsp;<span>00:00</span>\
                                </div>\
                                </div>');
    $videoCt.wrap($videoCtBox);
    $videoCt.after($videoCtControls);
    $videoCt.after($('<div class="video-prompt">视频加载中。。。</div>'));
    $videoCt.after($('<div class="video-fail">视频加载出错！下面为出错的地址：</div>'));
    $videoCt.after($('<span class="video-src"></span>'));
    videoDom(opts);

    var getVideoTimer;
    var comStayTimer = [];
    var comTimer = 0;
    var comStroeStatus = 0;
    var $video_container = $videoCt.parent('.video-player');
    var $videoCt_play = $('.video-play', $video_container);
    var $videoCt_seek = $('.video-seek', $video_container);
    var $videoCt_timer = $('.video-timer', $video_container);
    var $videoCt_clarity = $('.video-clarity', $video_container);

    $videoCt.attr('src',opts.clarity.src[0]);
    $('.video-seek video').attr('src', opts.clarity.src[0]);
    intOpts.claritySrc = opts.clarity.src[0];
    $videoCt_clarity.find('span').eq(0).addClass('disabled');
    $('#btnVideo').off('click').on('click',function(){
        $('.videos').show();
        getVideoInf();
    })
    $('.video-exit').off('click').on('click',function(){
        $('.videos').hide();
        $videoCt[0].pause();
    })
    var getVideoInf = function (){
        var setTimer;
        var key = true;
        if ($videoCt[0].readyState != 4 && $('.video-seek video')[0].readyState != 4) {
            $('.video-prompt').fadeOut();
            $('.video-src').html($('#video1').attr('src'));
            getVideoTimer = setTimeout(getVideoInf, 100);
        } else {
            $('.video-prompt').fadeOut();
            $('.video-fail').fadeOut();
            $('.video-src').fadeOut();
            //进度条/暂停播放
            if (!intOpts.currentTime) {
                intOpts.currentTime = $videoCt[0].currentTime;
            }
            $videoCt[0].currentTime = intOpts.currentTime;
            if (!intOpts.duration) {
                intOpts.duration = $videoCt[0].duration;
            }

            move($('.seek-slider'), $videoCt_seek, $('.seek-con'), 8, videoJump, $('.realTime'), true);
            $('.seek-slider').css('left', intOpts.currentTime / intOpts.duration * $('.video-seek').width() - 8);
            $('.seek-con').css('width', intOpts.currentTime / intOpts.duration * $('.video-seek').width());
            $('.video-seek').hover(function () {
                key = true;
                $(document).mousemove(function(e){
                    clearTimeout(setTimer);
                    if(key){hoverPlay(e);}
                });
            },function () {
                key = false;
                clearTimeout(setTimer);
                $('.video-seek .viewBox').fadeOut();
            });

            //时间
            $videoCt_timer.find('span').eq(0).text(gTimeFormat(intOpts.currentTime));
            $videoCt_timer.find('span').eq(1).text(gTimeFormat(intOpts.duration));

            //声音
            // if (!intOpts.volume) {
            //     intOpts.volume = opts.volume;
            // }
            // $videoCt[0].volume = intOpts.volume;
            // $videoCt_audio.find('.audio-button').removeClass('audio-mute');
            // move($('.audio-slider'), $('.audio-box'), $('.audio-con'), 6, audioJump);
            // $('.audio-slider').css('left', intOpts.volume * $('.audio-box').width() - 6);
            // $('.audio-con').css('width', intOpts.volume * $('.audio-box').width());
            // $videoCt_audio.find('.audio-button').off('click').on('click', function () {
            //     $(this).toggleClass('audio-mute');
            //     if ($videoCt[0].volume == 0) {
            //         $videoCt[0].volume = intOpts.volume;
            //     } else {
                    $videoCt[0].volume = 0;
            //     }
            // })

            //播放速度
            $('.video-playSpeed').off('click').on('click',function () {
                var value = parseFloat($(this).text().split('x').join(""));
                if(value >= 2){
                    value = 0.5;
                }
                value = value + 0.5;
                $videoCt[0].playbackRate = value;
                intOpts.playSpeed = value;
                $(this).text(value + 'x');
            });
        }
    }

    //slider
    function move(slider, box, con, radius, fn, vBox, vFollow) {
        var vBox = vBox || false;
        var vFollow = vFollow || false;
        var moveStatus = false;
        var sL = slider.offset().left;
        slider.click(function(){
        }).mousedown(function(e){
            moveStatus = true;
            sL = e.pageX - parseInt(slider.css('left'));
        })
        $(document).mousemove(function(e){
            if(moveStatus){
                var diffL = e.pageX - sL;
                show(diffL);
            }
        }).mouseup(function(){
            moveStatus = false;
        });
        box.off('click').on('click',function (e) {
            var diffL = e.pageX - box.offset().left - radius;
            show(diffL);
        });
        function show(x) {
            if( x >= -1*radius && x <= parseInt(box.width() - radius)){
                slider.css('left',x);
                con.css('width',x+radius);
                if(vBox){
                    var fnIndex = $videoCt[0].duration * (x+radius)/box.width();
                    intOpts.currentTime = fnIndex;
                    vBox.text(gTimeFormat(fnIndex));
                }else{
                    var fnIndex = (x+radius)/box.width();
                }
                fn(fnIndex);
            }
        }
        if(vFollow){
            //暂停播放
            var seTimer;
            var vPlay = function() {
                if(!intOpts.status) {
                    $videoCt[0].play();
                    intOpts.status = true;
                    seTimer = setInterval(function () {
                        var vprpo = $videoCt[0].currentTime/$videoCt[0].duration;
                        if(intOpts.cutover){
                            var vBTime = $videoCt[0].buffered.end(0)/$videoCt[0].duration;
                        }
                        if( vprpo < 1){
                            var tl = vprpo * (box.width() - radius);
                            var bL = vBTime * box.width();
                            intOpts.currentTime = $videoCt[0].currentTime;
                            vBox.text(gTimeFormat($videoCt[0].currentTime));
                            slider.css('left',tl);
                            con.css('width',tl+radius);
                            $('.seek-buffer').css('width',bL);
                            intStatus();
                            if(parseInt($videoCt[0].duration) - parseInt($videoCt[0].currentTime) <1){

                            }else{

                            }
                        }else if(vprpo == 1){
                            // clearInterval(seTimer);
                            intOpts.status = true;
                            return true;
                        }else{
                            clearInterval(seTimer);
                            intOpts.status = true;
                            return true;
                        }
                    },10);
                } else {
                    $videoCt[0].pause();
                    intOpts.status = false;
                    clearInterval(seTimer);
                }
            };
            if((intOpts.status || intOpts.cutover) && opts.autoplay){
                intOpts.status = false;
                vPlay();
            }

            $videoCt_play.removeClass('video-pause');
            $videoCt_play.off('click').on('click',vPlay);
            // $videoCt.off('click').on('click',vPlay);

            $videoCt.bind('play', function() {
                $videoCt_play.addClass('video-pause');
            });

            //退出视频暂停播放
            $('.video-exit').off('click').on('click',function(){
                $('.videos').hide()
                $videoCt[0].pause();
            })

            $videoCt.bind('pause', function() {
                $videoCt_play.removeClass('video-pause');
            });

            $videoCt.bind('ended', function() {
                $videoCt_play.removeClass('video-pause');
                  $videoCt[0].play();
            });
        }
    }

    //时间格式化
    var gTimeFormat = function (seconds) {
        var m = Math.floor(seconds / 60) < 10 ? "0" + Math.floor(seconds / 60) : Math.floor(seconds / 60);
        var s = Math.floor(seconds - (m * 60)) < 10 ? "0" + Math.floor(seconds - (m * 60)) : Math.floor(seconds - (m * 60));
        return m + ":" + s;
    };

    //进度条显示
    function hoverPlay(e) {
        var view = e.pageX - $('.video-seek').offset().left + 1;
        var time = $videoCt[0].duration * view / $('.video-seek').width();
        if(view <= 30){
            $('.video-seek .viewBox').css('left',-20);
            $('.video-seek .viewBox i').css('left',view+15);
        }else if(view >= ($('.video-seek').width()-30)){
            $('.video-seek .viewBox').css('left',$('.video-seek').width()-80);
            $('.video-seek .viewBox i').css('left',view-$('.video-seek').width()+75);
        }else{
            $('.video-seek .viewBox').css('left',view - 50);
            $('.video-seek .viewBox i').css('left',44);
        }
        $('.video-seek video')[0].currentTime = time;
        $('.video-seek span').text(gTimeFormat(time));
        $('.video-seek .viewBox').fadeIn();
    }

    //视频跳转
    function videoJump( timer ) {
        $videoCt[0].currentTime = timer;
    }
    //视频网络卡端
    function intStatus(){
        if($videoCt[0].readyState == 4){
            $('.video-status').fadeOut();
        }else{
            $('.video-status').fadeIn();
        }
    }

    //音量调节
    // function audioJump( volume ) {
    //     opts.volume = volume;
    //     $videoCt[0].volume = volume;
    // }

    //扩展功能
    function videoDom( opts ) {
        var exit=$('<!--退出按钮--><div class="video-exit"></div>')
        var playSpeed = $('<!--播放速度--><div class="video-playSpeed">1x</div>');
        if( opts.playSpeed ){playSpeed.appendTo($(".video-controls"))}
        $('.video-player').after(exit)
            $("video").mousedown(function(e){
                $(this).css("cursor","move");//改变鼠标指针的形状
                var offset = $(this).offset();//DIV在页面的位置
                var x = e.pageX - offset.left;//获得鼠标指针离DIV元素左边界的距离
                var y = e.pageY - offset.top;//获得鼠标指针离DIV元素上边界的距离
                $(document).bind("mousemove",function(ev){ //绑定鼠标的移动事件，因为光标在DIV元素外面也要有效果，所以要用doucment的事件，而不用DIV元素的事件
                    $(".videos").stop();//加上这个之后
                    var _x = ev.pageX - x;//获得X轴方向移动的值
                    var _y = ev.pageY - y;//获得Y轴方向移动的值
                    $(".videos").animate({left:_x+"px",top:_y+"px"},10);
                });
            });
            $(document).mouseup(function() {
                $(".videos").css("cursor","default");
                $(this).unbind("mousemove");
            });
    }

    return {
        title: '',
        status: true,
        currentTime: '0.00001',
        duration: '',
        cutover:true,
        volume: '',
        claritySrc: '',
        playSpeed: 1,
    }
}
