!function (e) {
    function t(a) {
        if (l[a])return l[a].exports;
        var n = l[a] = {exports: {}, id: a, loaded: !1};
        return e[a].call(n.exports, n, n.exports, t), n.loaded = !0, n.exports
    }

    var l = {};
    return t.m = e, t.c = l, t.p = "/", t(0)
}([function (e, t, l) {
    e.exports = l(1)
}, function (e, t, l) {
    "use strict";
    function a(e) {
        return e && e.__esModule ? e : {default: e}
    }

    var n = l(2), i = a(n), r = l(3);
    l(4);
    var u = i.default.createClass({
        displayName: "Tab2d", getInitialState: function () {
            return {txt: this.props.value}
        }, componentWillMount: function () {
            this.props.options && (this.props.value = this.props.value.split("\r\n").join("<br/>"))
        }, componentDidMount: function () {
            this.props.options && (this.props.value = this.props.value.split("\r\n").join("<br/>"))
        }, render: function () {
            return i.default.createElement("tr", null, i.default.createElement("td", {className: "tdL"}, this.props.title), i.default.createElement("td", {
                className: "tdR",
                colSpan: "3"
            }, i.default.createElement("span", {dangerouslySetInnerHTML: {__html: this.props.value}})))
        }
    }), d = i.default.createClass({
        displayName: "Tab4d", render: function () {
            return i.default.createElement("tr", null, i.default.createElement("td", {className: "tdL"}, this.props.title[0]), i.default.createElement("td", {className: "tdR"}, i.default.createElement("span", {dangerouslySetInnerHTML: {__html: this.props.value[0]}})), i.default.createElement("td", null, this.props.title[1]), i.default.createElement("td", {className: "tdR"}, i.default.createElement("span", {dangerouslySetInnerHTML: {__html: this.props.value[1]}})))
        }
    }), c = i.default.createClass({
        displayName: "Tab1d", render: function () {
            return i.default.createElement("tr", null, i.default.createElement("td", {
                colSpan: "5",
                className: "tdR"
            }, i.default.createElement("span", {dangerouslySetInnerHTML: {__html: this.props.value}})))
        }
    }), f = i.default.createClass({
        displayName: "CarDetail", getInitialState: function () {
            return {txt: "", showTrend: info.reportPriceTrend, pricetxt: "", avgPrice: ""}
        }, formateDate: function (e) {
            var t, l, a, n;
            new Date;
            return t = e.substring(0, e.indexOf("年")), l = e.substring(5, e.indexOf("月")), a = e.substring(8, e.indexOf("日")), n = t + "/" + l + "/" + a
        }, daysBetween: function (e, t) {
            var l, a, n, i = {};
            l = parseInt((Date.parse(e) - Date.parse(t)) / 31536e6), a = parseInt((Date.parse(e) - Date.parse(t)) / 2592e6), n = parseInt((Date.parse(e) - Date.parse(t)) / 864e5);
            var i = {y: l, m: a, d: n};
            return i
        }, chartMove: function () {
            var e = info.field_3040.value, t = this.formateDate(info.examedat), l = this.daysBetween(t, e), a = {
                init: function () {
                    var e, t = {width: document.body.clientWidth, height: 200}, a = 5, n = new Date, i = l,
                        r = info.examedat || n.getFullYear() + "年" + (n.getMonth() + 1 + "月");
                    e = info.field_4010 && info.field_4010.value ? info.field_4010.value / 1e4 : info.field_4012.value / 1e4;
                    var u, d, c = [], f = [];
                    c = this.setPrice(c, i, e), u = this.setVerticalNumber(c), d = this.coordinatePoints(c, u, t, a), this.setHorizontalNumber(f, r), this.createCanvas(d, t, a), this.showPriceNumber(d, a)
                }, handleTwigPrice: function (e, t) {
                    var l = {price: [], trendDate: []}, a = t.b2c_price, n = t.c2b_price;
                    if (e.length)for (var i = 0, r = e.length; i < r; i++)0 == i ? l.price.push(a) : l.price.push(a * (e[i].eval_price / n)), l.trendDate.push(e[i].trend_year);
                    return l
                }, setInnerText: function (e, t) {
                    "string" == typeof e.textContent ? e.textContent = t : e.innerText = t
                }, setPrice: function (e, t, l) {
                    console.log(t.m);
                    var a, n = document.getElementById("priceNumber").getElementsByTagName("span"), l = parseFloat(l),
                        i = function (e, t) {
                            switch (e) {
                                case 1:
                                case 2:
                                case 3:
                                    return .85 * t;
                                case 4:
                                case 5:
                                case 6:
                                case 7:
                                    return .9 * t;
                                default:
                                    return .95 * t
                            }
                        };
                    e.length || (a = t.m || t.d ? t.y + 1 : t.y, e.push(l), e.push(i(a + 1, e[0])), e.push(i(a + 2, e[1])));
                    for (var r = 0, u = e.length; r < u; r++)e[r] = parseFloat(e[r].toFixed(2)), e[r] = e[r] < .01 ? .01 : e[r], this.setInnerText(n[r], e[r]);
                    return e
                }, setHorizontalNumber: function (e, t) {
                    var l, a, n, i = document.getElementById("horizontalAxis").getElementsByTagName("span"), r = /\d+/g;
                    if (!e.length) {
                        l = parseInt(r.exec(t)[0]), a = parseInt(r.exec(t)[0]);
                        for (var u = 0; u < 3; u++)n = l + u + "." + a, e.push(n)
                    }
                    for (var u = 0, d = e.length; u < d; u++)this.setInnerText(i[u], e[u])
                }, setVerticalNumber: function (e) {
                    var t, l, a, n = document.getElementById("verticalAxis").getElementsByTagName("span"), i = [];
                    t = Math.ceil(Math.max.apply(null, e)), l = t.toString().split(".")[0].length, a = 1 != l ? Math.pow(10, l - 1) / 2 : 1, t % a != 0 && (t = Math.ceil(t / a) * a), i = [t, t / 2, 0];
                    for (var r = 0, u = n.length; r < u; r++)this.setInnerText(n[r], i[r]);
                    return t
                }, coordinatePoints: function (e, t, l, a) {
                    for (var n = null, i = [], r = 0, u = e.length; r < u; r++)n = {
                        x: Math.floor(333333 * l.width / 1e6 * (r + .5)),
                        y: l.height - Math.floor(l.height * (100 * e[r] / t * 100) / 1e4) + a
                    }, i.push(n);
                    return i
                }, createCanvas: function (e, t, l) {
                    var a = document.getElementById("canvasChart"), n = a.getContext("2d");
                    a.width = t.width, a.height = t.height + l, this.drawChart(n, e, t, l), this.drawPoints(n, e, t, l)
                }, drawChart: function (e, t, l, a) {
                    var n = e.createLinearGradient(0, 0, 0, l.height), i = t[0].y - a < a ? a : t[0].y - a;
                    n.addColorStop(0, "rgb(237,85,101)"), n.addColorStop(1, "rgb(251,221,224)"), e.fillStyle = n, e.beginPath(), e.moveTo(0, i);
                    for (var r = 0, u = t.length; r < u; r++)e.lineTo(t[r].x, t[r].y), r == u - 1 && e.lineTo(l.width, t[r].y + a);
                    e.lineTo(l.width, l.height + a), e.lineTo(0, l.height + a), e.lineTo(0, i), e.closePath(), e.fill()
                }, drawPoints: function (e, t, l, a) {
                    for (var n = a, i = 3, r = function (t, l, a) {
                        e.beginPath(), e.arc(a.x, a.y, t, 0, 2 * Math.PI, !1), e.fillStyle = l, e.closePath(), e.fill()
                    }, u = 0, d = t.length; u < d; u++)r(n, "#ed5766", t[u]), r(i, "white", t[u])
                }, showPriceNumber: function (e, t) {
                    var l = document.getElementById("priceNumber"), a = l.getElementsByTagName("span");
                    l.style.display = "block";
                    for (var n = 0, i = e.length; n < i; n++)a[n].innerText > .8 ? a[n].style.marginTop = e[n].y - 5 * t + "px" : a[n].innerText >= .5 && a[n].innerText < .8 ? a[n].style.marginTop = e[n].y - 7 * t + "px" : a[n].style.marginTop = e[n].y - 8 * t + "px"
                }
            }, n = document.getElementById("reportModuleFuturePrice");
            n && a.init()
        }, componentDidMount: function () {
            this.chartMove()
        }, componentWillMount: function () {
            info.purchasePrice && info.sellPrice ? info.field_4010.value && info.field_4012.value ? this.setState({pricetxt: info.field_4010.value / 1e4 + "万收购价/" + info.field_4012.value / 1e4 + "万销售价"}) : info.field_4010.value && !info.field_4012.value ? this.setState({pricetxt: info.field_4010.value / 1e4 + "万人民币"}) : !info.field_4010.value && info.field_4012.value && this.setState({pricetxt: info.field_4012.value / 1e4 + "万人民币"}) : info.purchasePrice && !info.sellPrice ? info.field_4010.value && this.setState({pricetxt: info.field_4010.value / 1e4 + "万人民币"}) : !info.purchasePrice && info.sellPrice && info.field_4012.value && this.setState({pricetxt: info.field_4012.value / 1e4 + "万人民币"}), null != info.averagePrice && null != info.biddingCount && 0 != info.averagePrice && 0 != info.biddingCount ? this.setState({avgPrice: "以上数据来自于拍卖平台，拍卖次数" + info.biddingCount + "次，平均价格" + info.averagePrice / 1e4 + "万人民币"}) : this.setState({avgPrice: ""})
        }, render: function () {
            return i.default.createElement("div", {className: "reportBox"}, i.default.createElement("div", {className: "reportH3 tc"}, i.default.createElement("p", {className: "p1"}, "车辆专业检测报告"), i.default.createElement("p", {className: "p2"}, "Cloud Inspection Report")), i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "报告概述")), i.default.createElement("div", {className: "itemInfo itemTd"}, i.default.createElement("table", null, i.default.createElement("tbody", null, i.default.createElement(u, {
                value: info.field_2040.value + " " + info.field_2010.value + " " + info.field_2020.value + " " + info.field_2030.value,
                title: "评估车型"
            }), i.default.createElement(u, {
                value: this.state.pricetxt,
                title: "评估价格"
            }), i.default.createElement(u, {
                value: info.field_4020.value / 1e4 + "万人民币",
                title: "新车指导价"
            }), i.default.createElement("tr", null, i.default.createElement("td", {
                colSpan: "2",
                className: "tdR",
                hidden: "" == this.state.avgPrice ? "hidden" : ""
            }, this.state.avgPrice)))))), i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "车辆基本信息")), i.default.createElement("div", {className: "itemInfo"}, i.default.createElement("table", null, i.default.createElement("tbody", null, i.default.createElement(d, {
                value: [info.field_1010.value, info.field_3010.value],
                title: ["牌照号", "表显里程(km)"]
            }), i.default.createElement(d, {
                value: [info.field_1060.value, info.field_3040.value],
                title: ["注册日期", "出厂日期"]
            }), i.default.createElement(d, {
                value: [info.field_3020.value, info.field_1070.value],
                title: ["排量L", "年检有效期"]
            }), i.default.createElement(d, {
                value: [info.field_3050.value, info.field_1020.value],
                title: ["座位数", "使用性质"]
            }), i.default.createElement(d, {
                value: [info.field_3030.value, info.field_3060.value],
                title: ["车身颜色", "车辆类型"]
            }), i.default.createElement(d, {
                value: [info.field_3080.value, info.field_3070.value],
                title: ["功率kw", "燃油类型"]
            }), i.default.createElement(u, {
                value: info.field_1040.value,
                title: "VIN码"
            }), i.default.createElement(u, {
                value: info.field_1030.value,
                title: "厂牌型号"
            }), i.default.createElement(u, {
                value: info.field_1050.value,
                title: "发动机号"
            }))))), i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "车辆配置")), i.default.createElement("div", {className: "itemInfo"}, i.default.createElement("table", null, i.default.createElement("tbody", null, i.default.createElement(d, {
                value: [info.field_3090.value, info.field_3100.value],
                title: ["环保标准", "变速形式"]
            }), i.default.createElement(d, {
                value: [info.field_3120.value, info.field_3110.value],
                title: ["驱动形式", "车门数"]
            }), i.default.createElement(d, {
                value: [info.field_3130.value, info.field_3070.value],
                title: ["进气方式", "供油系统"]
            }), i.default.createElement(d, {
                value: [info.field_3140.value, info.field_3160.value],
                title: ["天窗", "后排液晶显示器"]
            }), i.default.createElement(d, {
                value: [info.field_3170.value, info.field_3180.value],
                title: ["巡航系统", "空气悬架"]
            }), i.default.createElement(d, {
                value: [info.field_3190.value, info.field_3200.value],
                title: ["底盘升降", "自动大灯"]
            }), i.default.createElement(d, {
                value: [info.field_3210.value, info.field_3220.value],
                title: ["自动雨刮", "启动方式"]
            }), i.default.createElement(u, {
                value: info.field_3155.value,
                title: "空调"
            }), i.default.createElement(u, {
                value: info.field_3150.value.join("、"),
                title: "座椅"
            }))))), i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "车况概述")), i.default.createElement("div", {className: "itemInfo"}, i.default.createElement("table", null, i.default.createElement("tbody", null, i.default.createElement(u, {
                value: info.field_4140.value ? info.field_4140.value : "无",
                title: "特殊情况"
            }), i.default.createElement(u, {
                value: info.field_4150.value ? info.field_4150.value : "无",
                title: "结论综述"
            }))))), i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "配置概述")), i.default.createElement("div", {className: "itemInfo"}, i.default.createElement("table", null, i.default.createElement("tbody", null, i.default.createElement(d, {
                value: [info.field_3030.value, info.field_3140.value],
                title: ["车身颜色", "天窗"]
            }), i.default.createElement(u, {
                value: info.field_3155.value,
                title: "空调"
            }), i.default.createElement(u, {
                value: info.field_3150.value.join("、"),
                title: "座椅"
            }))))), i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "特殊车管业务说明")), i.default.createElement("div", {className: "itemInfo"}, i.default.createElement("table", null, i.default.createElement("tbody", null, i.default.createElement(u, {
                value: info.field_1080.value,
                title: "过户次数"
            }), i.default.createElement(u, {
                value: info.field_1080.options ? info.field_1080.options : "无",
                title: "过户详情",
                options: !0
            }))))), info.reportPrice ? i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "车辆价格影响因素")), i.default.createElement("div", {className: "itemInfo"}, i.default.createElement("div", {className: "priceIntr"}, i.default.createElement("div", {className: "intrTit"}, i.default.createElement("span", {className: "titIcon smIcon"}), "新车价及二手车价说明"), i.default.createElement("p", {className: "intrTxt"}, "该车型当前新车价", info.field_4020.value / 1e4, "万，优惠价", info.field_4020.value / 1e4, "万。该车购置税", info.field_4030.value / 1e4, "万，购入价", info.field_4040.value / 1e4, "万。该车型二手车估价中，涉及车龄调整", info.field_4060.value, "，成新率", info.field_4050.value, "。", i.default.createElement("br", null), "二手车辆的价格需要参考当前新车的价格走势。如果新车价格出现波幅，二手车价格也会相应出现波幅。")), i.default.createElement("div", {className: "priceIntr"}, i.default.createElement("div", {className: "intrTit"}, i.default.createElement("span", {className: "titIcon yxIcon"}), "价格影响因素"), i.default.createElement("div", {className: "caseList"}, i.default.createElement("div", {className: "caseItem"}, i.default.createElement("em", null), "更新换代", info.field_4080.value), i.default.createElement("div", {className: "caseItem"}, i.default.createElement("em", null), "公里系数", info.field_4100.value), i.default.createElement("div", {className: "caseItem"}, i.default.createElement("em", null), "颜色系数", info.field_4120.value), i.default.createElement("div", {className: "caseItem"}, i.default.createElement("em", null), "车辆版本系数", info.field_4090.value), i.default.createElement("div", {className: "caseItem"}, i.default.createElement("em", null), "市场冷热", info.field_4070.value), i.default.createElement("div", {className: "caseItem"}, i.default.createElement("em", null), "车况等级7%"), i.default.createElement("div", {className: "caseItem"}, i.default.createElement("em", null), "整修费用", info.field_4130.value / 1e4, "万"))))) : "", this.state.showTrend ? i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "车辆未来价格趋势")), i.default.createElement("div", {className: "itemInfo"}, i.default.createElement("div", {
                className: "appraisal-report-module report-module-future-price",
                id: "reportModuleFuturePrice"
            }, i.default.createElement("div", {className: "module-content color-gray"}, i.default.createElement("div", {className: "unit text-left"}, "单位：万元"), i.default.createElement("div", {className: "chart"}, i.default.createElement("div", {className: "chart-content"}, i.default.createElement("canvas", {id: "canvasChart"}), i.default.createElement("div", {
                className: "price-number",
                id: "priceNumber"
            }, i.default.createElement("span", {className: "value price-0"}), i.default.createElement("span", {className: "value price-1"}), i.default.createElement("span", {className: "value price-2"}))), i.default.createElement("div", {
                className: "horizontal-axis clearfix",
                id: "horizontalAxis"
            }, i.default.createElement("span", {className: "value horizontal-0"}), i.default.createElement("span", {className: "value horizontal-1"}), i.default.createElement("span", {className: "value horizontal-2"})), i.default.createElement("div", {
                className: "vertical-axis",
                id: "verticalAxis"
            }, i.default.createElement("span", {className: "value vertical-0"}), i.default.createElement("span", {className: "value vertical-1"}), i.default.createElement("span", {className: "value vertical-2"}))))))) : "", info.maintainData ? i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "维修保养记录")), i.default.createElement("div", {className: "itemInfo"}, i.default.createElement("table", null, i.default.createElement("tbody", null, info.maintainData.map(function (e) {
                return i.default.createElement("section", null, i.default.createElement(u, {
                    value: e.kilometers + "公里",
                    title: e.date
                }), i.default.createElement(c, {value: e.content}))
            }))))) : "", info.insuranceData ? i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "车辆保险信息")), i.default.createElement("div", {className: "itemInfo"}, i.default.createElement("table", null, i.default.createElement("tbody", null, info.insuranceData.map(function (e) {
                return i.default.createElement("section", null, i.default.createElement(d, {
                    value: [e.date, e.totalFee + "元"],
                    title: ["事故日期", "核损费用"]
                }), i.default.createElement(u, {
                    value: e.description,
                    title: "事故描述"
                }), i.default.createElement(u, {value: e.detail, title: "细节"}))
            }))))) : "", info.historyPrices ? i.default.createElement("div", {className: "reportItem"}, i.default.createElement("div", {className: "itemTit"}, i.default.createElement("div", null, "历史拍卖价格")), i.default.createElement("div", {className: "itemInfo"}, i.default.createElement("table", null, i.default.createElement("tbody", null, info.historyPrices.map(function (e) {
                return i.default.createElement("section", null, i.default.createElement(d, {
                    value: [e.bidTime, e.bidPrice / 1e4 + "万"],
                    title: ["拍卖时间", "拍卖价格"]
                }), i.default.createElement(u, {
                    value: "城市：" + e.city + "，上牌时间：" + e.regist + "，公里：" + e.mileage + "公里，车况：" + e.rating,
                    title: "车况详情"
                }))
            }))))) : "", i.default.createElement("div", {className: "lowIntr"}, i.default.createElement("p", null, "法律声明："), i.default.createElement("p", null, "一、本报告评估日期为", info.examedat, "，评估结果基于麦拉云检测应用中所采集的车辆照片体现的车辆状态生成，有效期为车辆状态未发生改变的15个自然日。"), i.default.createElement("p", null, "二、本评估结果根据车辆正常行驶，车辆提交评估时所在地、及当地该车型供需关系等因素综合形成，且存在一定的容错率。"), i.default.createElement("p", null, "三、在评估有效期内，如车况有变动，市场价格有变动，则需重新评估价格。如果超过有效期，则需进入第二次评估价格阶段"), i.default.createElement("div", {className: "mlogo"}, i.default.createElement("span", null))))
        }
    }), s = i.default.createElement(r.Router, {history: r.hashHistory}, i.default.createElement(r.Route, {
        path: "/",
        component: f
    }));
    ReactDOM.render(s, document.getElementById("carDetail"))
}, function (e, t) {
    e.exports = React
}, function (e, t) {
    e.exports = ReactRouter
}, function (e, t) {
}]);