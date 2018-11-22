//新表格标题行和首尾列固定
var tableNewFix = {
    init: function () {
        this.$tableNewBox = $(".table-new-box[data-table-fix]");
        this.$tableDefault = this.$tableNewBox.find(".table-default");
        this.$tableWraper = this.$tableDefault.find(".table-wraper");
        this.$tableContent = this.$tableDefault.find(".table-content");
        this.$table = this.$tableContent.find("table");
        this.$notContent = $(".list-content .list-not-content");
        this.hadContent = this.$table.find("tbody tr").length;
        this.start();
    },
    start: function () {
        var fix = this.$tableNewBox.data("tableFix");
        var reg = /\S+/;
        var $CustomTips, colspan;

        if (!this.$tableNewBox.length) {
            if (this.$notContent.length) {
                this.listNotContent();
            }
            return;
        }

        if (!this.hadContent) {
            colspan = this.$table.find("thead th").length;
            this.$table.find("tbody").append('<tr><td class="t-center had-icon" colspan="' + colspan + '"></td></tr>')
        }

        this.$tableNewBox.show();

        this.height_thead = this.$table.find("thead").height();

        this.$table.find("td").each(function (index, el) {
            if (!reg.test($(this).text())) {
                $(this).text("-");
            }
        });

        $CustomTips = this.$table.find("td").filter("[data-custom-tips]");

        customTips.init($CustomTips);

        this.getScrollWidth();
        this.restructureBoxHeight();
        this.createHeadFix();
        if (fix == "all") {
            this.createFix("start");
            this.createFix("end");
        } else if (fix == "start") {
            this.createFix("start");
        } else if (fix == "end") {
            this.createFix("end");
        }

        this.handle();
    },
    handle: function () {
        var self = this;
        self.$tableContent.on("scroll", function () {
            var scrollTop = $(this).scrollTop();
            var scrollLeft = $(this).scrollLeft();
            self.$tableHeadFix && self.$tableHeadFix.css("marginLeft", -scrollLeft);
            self.$tableStartFix && self.$tableStartFix.css("marginTop", -scrollTop);
            self.$tableEndFix && self.$tableEndFix.css("marginTop", -scrollTop);
        })
        $(window).resize(function (event) {
            self.restructureBoxHeight();
            self.restructure();
        });
    },
    listNotContent: function () {
        var height = this.restructureBoxHeight(true);
        if (height > 300) {
            height = 300;
        }
        this.$notContent.css({
            "height": height,
            "padding-top": height / 2 - 20
        })
    },
    getScrollWidth: function () {
        var width_actual;
        this.$tableNewBox.css("height", 100);
        this.$tableContent.css("height", 100);
        this.$tableContent.prepend('<div id="scroll" style="height:80px; visibility:hidden;"></div>');
        width_actual = this.$tableContent.find("#scroll").width();
        this.$tableContent.find("#scroll").remove();
        this.scrollWidth = this.$tableContent.width() - width_actual;
        this.$tableNewBox.css("height", "auto");
        this.$tableContent.css("height", "auto");
    },
    createHeadFix: function () {
        var $tableClone = this.$table.clone();

        this.$tableDefault.prepend('<div class="table-head-fix-box"><div class="table-head-fix"></div></div>');
        this.$tableHeadFix = this.$tableDefault.find(".table-head-fix");

        this.$tableHeadFix.append($tableClone);

        this.$tableHeadFix.css({
            height: this.height_thead
        })

        this.restructureFix("head", this.$tableHeadFix);

        this.$tableDefault = this.$tableNewBox.find(".table-default");

        $tableClone.find("tbody").remove();
        this.$table.css("marginTop", -this.height_thead);
    },
    createFix: function (type) {
        var $tableDefaultClone = this.$tableDefault.clone();
        var className = type == "start" ? "table-start-fix" : "table-end-fix";
        this.$tableNewBox.prepend($tableDefaultClone);
        $tableDefaultClone.toggleClass('table-default ' + className);
        $tableDefaultClone.find(".table-head-fix").css('width', 'auto');

        if (type == "start") {
            this.$tableStartFix = $tableDefaultClone.find(".table-content");
        } else {
            this.$tableEndFix = $tableDefaultClone.find(".table-content");
        }

        $tableDefaultClone.find(".table-content").css({
            "height": "auto"
        });

        this.restructureFix(type, $tableDefaultClone);

        $tableDefaultClone.find("tr").each(function (index, el) {
            var $item, $itemRemvoe;
            if (type == "start") {
                $item = $(this).find("td").first();
                $itemRemvoe = $(this).find("td,th").not(":first");
            } else {
                $item = $(this).find("td").last();
                $itemRemvoe = $(this).find("td,th").not(":last");
            }
            $itemRemvoe.remove();
        });
    },
    restructureFix: function (type, $el) {
        var width_table = 0;
        this.$table.find("th").each(function () {
            var _width = $(this).outerWidth();
            width_table += _width;
        })
        var height_table = this.$table.height();
        var hasHorizontalScroll = this.hasHorizontalScroll(width_table);
        var hasVerticalScroll = this.hasVerticalScroll(height_table);
        var startWidth = this.$tableContent.find("th").first().width();
        var endWidth = this.$tableContent.find("th").last().width();
        var self = this;

        if (!($el && $el.length)) {
            return;
        }
        if (type == "head") {
            $el.css({
                "width": width_table
            });
            $el.find("thead th").each(function (index, el) {
                var $text = $("<div>" + $(this).text() + "</div>");
                var width = self.$table.find("thead th").eq(index).outerWidth() - 40;
                $(this).html($text);
                $text.css("width", width);
            });
        } else {
            $el.css({
                "height": hasHorizontalScroll ? this.$tableNewBox.outerHeight() - this.scrollWidth : this.$tableNewBox.outerHeight()
            });
            if (type == "end") {
                $el.find("th,td").width(endWidth);
                $el.css({
                    "right": hasVerticalScroll ? this.scrollWidth : 0
                });
            } else {
                $el.find("th,td").width(startWidth);
            }
            this.$tableDefault.find(".table-head-fix-box").toggleClass('hadCover', hasVerticalScroll);
        }
    },
    restructure: function () {
        if (!(this.$tableNewBox.length && this.hadContent)) {
            return;
        }

        this.restructureFix("head", this.$tableDefault.find(".table-head-fix"));
        this.restructureFix("start", this.$tableNewBox.find(".table-start-fix"));
        this.restructureFix("end", this.$tableNewBox.find(".table-end-fix"));
    },
    restructureBoxHeight: function (onlyHeight) {
        var height_window = $(window).height();
        var $listContent = $(".list-content");
        var height_navigation = $listContent.find(".navigation").outerHeight(true) || 0;
        var height_copyright = $listContent.next(".copyright").outerHeight(true) || 0;
        var paddingBottom = parseInt($listContent.css("paddingBottom")) || 0;

        var offset = this.$tableNewBox.offset();

        if (onlyHeight) {
            offset = this.$notContent.offset();
            return parseInt(height_window - offset.top - height_navigation - height_copyright - paddingBottom) - 2;
        }

        if (!(this.$tableNewBox.length && this.hadContent)) {
            return;
        }

        this.height_box = parseInt(height_window - offset.top - height_navigation - height_copyright - paddingBottom) - 2;

        this.$tableNewBox.css("max-height", this.height_box);

        if (this.$table.height() > this.height_box) {
            this.$tableContent.css("height", this.height_box - this.height_thead);
        } else {
            this.$tableContent.css("height", "auto");
        }
    },

    hasHorizontalScroll: function (width_table) {
        return (width_table > this.$tableWraper.width()) ? true : false;
    },
    hasVerticalScroll: function (height_table) {
        return (height_table > this.$tableWraper.height()) ? true : false;
    }
}



