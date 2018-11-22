//业务方法
var workMethod = {
    //分页跳转
    pageTo: function ($input) {
        $input.on("keydown", function (e) {
            var reg = /^[1-9]\d*$/;
            var value = $.trim($(this).val());
            var key = e.whick || e.keyCode;
            if (value && key == 13) {
                if (!reg.test(value) || parseInt(value) > parseInt($(this).data("pagecount"))) {
                    commonMethod.showTopTips("页码错误");
                    return false;
                } else {
                    location.href = $(this).data("route").replace("pageCount", value);
                }
            }
        })
    },
    //统计"复审"和"已退回"状态数量
    getCount: function (path) {
        $.ajax({
            url: path,
            type: 'post',
            dataType: 'json',
            success: function (data) {
                if (data.backCount) {
                    $('.back-count').html(data.backCount);
                } else if (data.recheckCount) {
                    $('.recheck-count').html(data.recheckCount);
                }
            }
        });
    },
    //退出账户
    logout: function () {
        $('#logout').click(function (event) {
            var path = $(this).attr("href");
            event.preventDefault();
            commonMethod.prompt({
                title: "退出账户",
                msgHTML: "确定要退出系统吗？",
                confirmTxt: "退出",
                cancelTxt: "留下",
                confirm: function () {
                    window.location.href = path;
                }
            })
        });
    },
    //设置导航
    setSideBarNav: function () {
        var $sideMenu = $("#sideMenu");
        var sideMenuEvent = function () {
            var $btnCtrl = $(".custom-navbar .sidebar .ico-arrow-row");
            var $wrapper = $("#wrapper");
            var menuState = window.localStorage ? localStorage.getItem("menuState") : 0;
            var $iconFontSpan = $(".custom-navbar .sidebar .ico-arrow-row span");
            var $element;
            var isIE = commonMethod.isIE();
            var toggleBtn = function (state) {
                if (state == 1) {
                    $iconFontSpan.removeClass('icon-font-zhankai').addClass('icon-font-shouqi');
                    $iconFontSpan.parent().attr("title", "展开");
                } else {
                    $iconFontSpan.removeClass('icon-font-shouqi').addClass('icon-font-zhankai');
                    $iconFontSpan.parent().attr("title", "收起");
                }
            }

            //根据当前url设置菜单默认打开项
            $element = $sideMenu.find('.nav-second-level a').filter(function () {
                var currentUrl = window.location.href;
                var active = false;
                if (currentUrl.indexOf(this.href) !== -1) {
                    active = true;
                }

                return active;
            }).addClass('active').parents(".nav-second-level").addClass('collapse in');

            $element.parents('.nav-first-level').addClass('current');

            if (menuState == 1) {
                $wrapper.addClass('wrapper-shrink');

                tableNewFix && tableNewFix.restructure();
            }
            toggleBtn(menuState);

            $sideMenu.on("click", ">li>a", function () {
                var $parent = $(this).parent();
                var $siblings = $parent.siblings();
                var $children = $siblings.find("ul.in");
                var $next = $(this).next(".nav-second-level");

                if ($wrapper.hasClass('wrapper-shrink')) {
                    return false;
                }
                $parent.toggleClass('current');
                if (isIE <= 9) {
                    $children.removeClass('in');
                    $next.toggleClass('in');
                } else {
                    $children.collapse('toggle');
                    $next.collapse('toggle');
                }
                $siblings.removeClass('current');
                return false;
            })

            $btnCtrl.on("click", function () {
                var state;
                $wrapper.toggleClass('wrapper-shrink');
                state = $wrapper.hasClass('wrapper-shrink') ? 1 : 0;
                toggleBtn(state);
                if (window.localStorage) {
                    localStorage.setItem("menuState", state)
                }
                tableNewFix && tableNewFix.restructure();
            })
        }

        $sideMenu.length && sideMenuEvent();
    },
    //图片列表加载
    loadImageList: function ($el) {
        $el.find("img").each(function () {
            var $img = $(this);
            var url = $img.data("cut");
            if (url) {
                commonMethod.loadImage(url, function (img) {
                    $img.attr("src", url).removeAttr('data-cut');
                })
            }
        })
    },
    //设置日期插件
    setDatepicker: function ($el, options) {
        var common = {
            language: 'zh-CN',
            format: 'yyyy/mm/dd',
            orientation: "bottom left",

            autoclose: true,
            endDate: "today",
            todayHighlight: true
        };

        $.extend(common, options);

        $el.datepicker(common);
    },
    //下载报表
    downloadReport: function () {
        $(".download-report").on("click", function () {
            var query = location.search;
            var url = $(this).data("download-url") + query;
            commonMethod.prompt({
                title: "下载报表",
                msgHTML: "确定要下载吗？",
                confirm: function () {
                    window.open(url + query);
                }
            })
        })
    },
    //初始化放大图片插件
    setInitViewer: function ($el, url, isDestroy) {
        if (isDestroy) {
            $el.viewer("destroy");
        }
        $el.viewer({
            url: url,
        });
    },
    //退回理由展示
    showReason: function () {
        $(".reason-list .view-more").each(function () {
            var timer = null;
            var $tipPosition = $(this).parents("li").find(".tip-position");

            $(this).hover(
                function () {
                    clearTimeout(timer);
                    $tipPosition.show();
                },
                function () {
                    timer = setTimeout(function () {
                        $tipPosition.hide();
                    }, 500)
                }
            )
            $tipPosition.hover(
                function () {
                    clearTimeout(timer);
                },
                function () {
                    timer = setTimeout(function () {
                        $tipPosition.hide();
                    }, 500)
                }
            )
        })
    },
    //改版提示
    changeVersionWithTip: function () {
        var isShowTip = true;
        var functionDotHTML = '<div class="function-dot"></div>';
        var functionTipHTML = '<div class="function-tip">'
            + '<div class="function-header">功能提示</div>'
            + '<div class="function-content">我们把修改密码以及退出账户放在这里啦</div>'
            + '<div class="function-footer"><span class="btn-know">我知道了</span></div>'
            + '</div>';
        if (document.cookie.length > 0) {
            isShowTip = (document.cookie.indexOf("isShowTip=") != -1) ? false : true;
        }

        if (isShowTip) {
            $(".navbar-header.pull-right").append(functionDotHTML + functionTipHTML);
            commonMethod.setCookie("isShowTip", "show", 365, "/");
            $(".navbar-header.pull-right .function-tip").find(" .btn-know").on("click", function () {
                $(".navbar-header.pull-right .function-dot").remove();
                $(".navbar-header.pull-right .function-tip").remove();
            })
        }
    }
}
