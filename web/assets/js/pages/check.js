$(function() {
  //需用到的字符串
  var STR_DL_HORIZONTAL = "dl-horizontal";
  var STR_CLEARFIX = "clearfix";
  var STR_CHOOSE_GROUP = "btn-choose-group";
  var STR_UNDER = "under";
  var STR_BTN_CONDITION = "btn-condition";
  var STR_SMALL_STYLE = "small-style";
  var STR_ONLY_ROW = "only-row";
  var STR_TOGGLE_BTN_GROUP = "list-inline " + STR_CHOOSE_GROUP;
  var STR_CHECK_CHOOSE_GROUP = "check-choose-group";
  var STR_CHOOSE = "choose";
  var STR_ATTACHED = "." + "attached";
  var STR_COMPONENT = "." + "component";
  var STR_NOT_ATTACHED = "li:not('" + STR_ATTACHED + "')";
  var STR_SPREAD = "spread";
  var STR_RETRACT = "retract";
  var STR_BTN_SPREAD_OR_RETRACT = "btn-spreadOrRetract";
  var STR_TIP = "tip";
  var STR_RADIO_TYPE = "radioType";
  var STR_CHECK_TYPE = "checkType";
  var STR_TEXT_TYPE = "textType";
  var STR_TEXTAREA_TYPE = "textareaType";
  var STR_CHOOSE_OTHER = "choose-other";
  var STR_TAB_PANE = "tab-pane";
  var STR_NUMBER = "number";
  var STR_PRECENT = "percent";
  var STR_INPUT_RED = "input-red";
  var STR_SEARCH_MAINTAIN_BTN = "search-maintain-btn";
  var STR_NOTICE_AREA = "notice-area";
  var STR_SHOW_POPUP = "show-popup";
  var STR_USE_THIS_APPRAISAL = "use-this-appraisal";

  var STR_OTHER = "其它";
  var STR_SPREAD_TEXT = "展开更多";
  var STR_RETRACT_TEXT = "收起展开";
  var STR_SPREAD_TEXT_APPRAISAL = "展开";
  var STR_RETRACT_TEXT_APPRAISAL = "收起";
  var STR_DATA_NUM_ROWS = "numrows";
  var STR_MESSAGE_VALIDATE = "表单还未填写完整，或填写的数据有误";
  var STR_MESSAGE_SAVE_SUCCESS = "保存成功！";
  var STR_MESSAGE_SAVE_FAILED = "服务器错误！请重试";
  var STR_MESSAGE_CHELING = "车龄调整不能为负数，请重新调整";
  var STR_MESSAGE_MAINTAINA = "当前维修记录查询结果还没产生，是否提交？";
  var STR_MESSAGE_LSJ = "当前老司机记录查询结果还没产生，是否提交？";
  var STR_MESSAGE_MAINTAINA_CONFIRM = "是否确定查询";
  var STR_MESSAGE_MAINTAINA_YET = "当前vin码已查询过";

  var STR_MESSAGE_MAINTAINA_ERROR = "请输入发动机号/牌照号码";
  var STR_MESSAGE_APPRAISAL_CITY = "请填写城市";
  var STR_MESSAGE_APPRAISAL_COLOR = "请选择车身颜色";
  var STR_MESSAGE_APPRAISAL_SELF = "请填写完整信息";
  var STR_MESSAGE_VIN_SUBMIT = "VIN码校验不符合17位校验规则，是否提交？";
  var STR_MESSAGE_VIN_SELECT = "VIN码校验不符合17位校验规则，是否查询？";
  var STR_MESSAGE_VIN_MASSAGE = "VIN码校验不符合17位校验规则！如果VIN码不是17位请忽略此提示";



  //需用到的数字
  var NUM_LINE_WRAP_LENGTH_1 = 4; //选项(单选、多选)文字超过多少个作折行处理
  var NUM_LINE_WRAP_LENGTH_2 = 6; //表单项标题超过多少个作折行处理
  var NUM_CONDITION_ROW_VALUE = 4; //选项(单选、多选)一行显示个数
  var NUM_MAINTAIN_TIME = 60000; //每隔多长时间获取维修记录和老司机记录

  //数据验证
  var REG_VIN = /(^[A-Za-z0-9]{17}|[A-Za-z0-9]{9})$/;

  var REG_DATE = /^\d{4}[\/-]\d{2}[\/-]\d{2}$/;

  //区块
  var $checkFormBox = $("#formCheckBox");
  var $formCheck = $("#formCheck");
  var $checkHeaderFix = $("#checkHeaderFix");

  //5步
  var $step1 = $("#step1");
  var $step2 = $("#step2");
  var $step3 = $("#step3");
  var $step4 = $("#step4");
  var $step5 = $("#step5");
  var $step6 = $("#step6");

  //审核相关按钮
  var $btnSave = $checkHeaderFix.find("#btnSave");
  var $btnUntread = $checkHeaderFix.find("#btnUntread");
  var $btnReadySubmit = $("#btnReadySubmit");
  var $returnTaskList = $('#returnTaskList');

  //所有表单项
  //tab step1(基本信息)
  var $formText_PaiZhaoHaoMa = $formCheck.find("input[name='form[field_1010]']");
  var $formRadio_ShiYongXingZhi = $formCheck.find("input[name='form[field_1020][value]']");
  var $formRadio_ShiYongXingZhiOther = $formCheck.find("input[name='form[field_1020][append][text]']");
  var $formCheck_ShiFouPingXing = $formCheck.find("input[name='form[field_1021][value][]']");
  var $formText_ChangPaiXingHao = $formCheck.find("input[name='form[field_1030]']");
  var $formText_VIN = $formCheck.find("input[name='form[field_1040]']");
  var $formText_Engine = $formCheck.find("input[name='form[field_1050]']");
  var $formText_FaDongJiHao = $formCheck.find("input[name='form[field_1050]']");
  var $formRadio_RanYouLeiXing = $formCheck.find("input[name='form[field_3070][value]']");
  var $formRadio_RanYouLeiXingOther = $formCheck.find("input[name='form[field_3070][append][text]']");
  var $formRadio_CheShenYanSe = $formCheck.find("input[name='form[field_3030][value]']");
  var $formRadio_CheShenYanSeOther = $formCheck.find("input[name='form[field_3030][append][text]']");
  var $formText_PaiLiang = $formCheck.find("input[name='form[field_3020]']");
  var $formText_GongLv = $formCheck.find("input[name='form[field_3080]']");
  var $formRadio_ZuoWeiShu = $formCheck.find("input[name='form[field_3050][value]']");
  var $formRadio_ZuoWeiShuOther = $formCheck.find("input[name='form[field_3050][append][text]']");
  var $formText_ChuChangRiQi = $formCheck.find("input[name='form[field_3040][value]']");
  var $formText_ZhuCeRiQi = $formCheck.find("input[name='form[field_1060][value]']");
  var $formRadio_ShiFouZhuCe = $formCheck.find("input[name='form[field_1060][append][radio]']");
  var $formText_NianShenYouXiaoQi = $formCheck.find("input[name='form[field_1070][value]']");
  var $formRadio_GuoHuCiShu = $formCheck.find("input[name='form[field_1080][value]']");
  var $formRadio_GuoHuCiShuOther = $formCheck.find("input[name='form[field_1080][append][text]']");
  var $formTextarea_GuoHuXiangXi = $formCheck.find("textarea[name='form[field_1080][append][textarea]']");

  //tab step3(确定车型)
  var $formText_PinPai = $formCheck.find("input[name='form[field_2010]']");
  var $formText_CheXi = $formCheck.find("input[name='form[field_2020]']");
  var $formText_CheXing = $formCheck.find("input[name='form[field_2030]']");
  var $formText_NianKuan = $formCheck.find("input[name='form[field_2040]']");
  var $formText_ModelId = $formCheck.find("input[name='form[field_2031]']");

  //tab step4(车辆配置)
  var $formText_BiaoXianLiCheng = $formCheck.find("input[name='form[field_3010]']");
  var $formRadio_CheLiangLeiXing = $formCheck.find("input[name='form[field_3060][value]']");
  var $formRadio_CheLiangLeiXingOther = $formCheck.find("input[name='form[field_3060][append][text]']");
  var $formRadio_HuanBaoBiaoZhun = $formCheck.find("input[name='form[field_3090][value]']");
  var $formRadio_HuanBaoBiaoZhunOther = $formCheck.find("input[name='form[field_3090][append][text]']");
  var $formRadio_BianSuXingShi = $formCheck.find("input[name='form[field_3100][value]']");
  var $formRadio_BianSuXingShiOther = $formCheck.find("input[name='form[field_3100][append][text]']");
  var $formRadio_CheMenShu = $formCheck.find("input[name='form[field_3110][value]']");
  var $formRadio_CheMenShuOther = $formCheck.find("input[name='form[field_3110][append][text]']");
  var $formRadio_QuDongXingShi = $formCheck.find("input[name='form[field_3120][value]']");
  var $formRadio_QuDongXingShiOther = $formCheck.find("input[name='form[field_3120][append][text]']");
  var $formRadio_JinQiFangShi = $formCheck.find("input[name='form[field_3130][value]']");
  var $formRadio_JinQiFangShiOther = $formCheck.find("input[name='form[field_3130][append][text]']");
  var $formRadio_TianChuang = $formCheck.find("input[name='form[field_3140][value]']");
  var $formRadio_TianChuangOther = $formCheck.find("input[name='form[field_3140][append][text]']");
  var $formCheck_ZuoYi = $formCheck.find("input[name='form[field_3150][value][]']");
  var $formCheck_KongTiao = $formCheck.find("input[name='form[field_3155][value][]']");
  var $formRadio_HouPaiYeJing = $formCheck.find("input[name='form[field_3160][value]']");
  var $formRadio_XunHang = $formCheck.find("input[name='form[field_3170][value]']");
  var $formRadio_KongQiXuanJia = $formCheck.find("input[name='form[field_3180][value]']");
  var $formRadio_DiPanShengJiang = $formCheck.find("input[name='form[field_3190][value]']");
  var $formRadio_ZiDongDaDeng = $formCheck.find("input[name='form[field_3200][value]']");
  var $formRadio_ZiDongYuGua = $formCheck.find("input[name='form[field_3210][value]']");
  var $formRadio_QiDongFangShi = $formCheck.find("input[name='form[field_3220][value]']");
  var $formRadio_QiDongFangShiOther = $formCheck.find("input[name='form[field_3220][append][text]']");
  var $formText_ShangPaiShiJian = $formCheck.find("input[name='form[field_1060][value]']");

  //tab step5(车辆总结)
  var $formCheck_TeShuCheKuang = $formCheck.find("input[name='form[field_4140][value][]']");
  var $formText_CheKuangZongJie = $formCheck.find("input[name='form[field_4150]']");
  var $formRadio_PingGuJieGuo = $formCheck.find("input[name='form[field_result][value]']");
  var $formTextarea_JuJueLiYou = $formCheck.find("textarea[name='form[field_result][append][textarea]']");

  //tab step6(车辆核价)
  var $formText_ShouGouJia = $formCheck.find("input[name='form[field_4010]']");
  var $formText_XiaoShouJia = $formCheck.find("input[name='form[field_4012]']");
  var $formText_WeilaiJia = $formCheck.find("input[name='form[field_4014]']");


  //同标价(收购价),同标价(销售价),车况价,选配价,第三方价 
  var $formText_tbj_sgj = $formCheck.find("input[name='form[field_5010]']");
  var $formText_tbj_xsj = $formCheck.find("input[name='form[field_5011]']");
  var $formText_ckj = $formCheck.find("input[name='form[field_5012]']");
  var $formText_xpj = $formCheck.find("input[name='form[field_5013]']");
  var $formText_dsf = $formCheck.find("input[name='form[field_5014]']");

  var $formText_TiaoZhengZhiDaoJia = $formCheck.find("input[name='form[field_4020]']");
  var $formText_GouZhiShui = $formCheck.find("input[name='form[field_4030]']");
  var $formText_GouRuJia = $formCheck.find("input[name='form[field_4040]']");
  var $formText_ChengXinLv = $formCheck.find("input[name='form[field_4050]']");
  var $formText_CheLingTiaoZheng = $formCheck.find("input[name='form[field_4060]']");
  var $formText_ShiChangLengRe = $formCheck.find("input[name='form[field_4070]']");
  var $formText_GengXinHuanDai = $formCheck.find("input[name='form[field_4080]']");
  var $formText_CheLiangBanBenXiShu = $formCheck.find("input[name='form[field_4090]']");
  var $formText_GongLiXiShu = $formCheck.find("input[name='form[field_4100]']");
  var $formText_CheKuangDengJi = $formCheck.find("input[name='form[field_4110]']");
  var $formText_YanSeXiShu = $formCheck.find("input[name='form[field_4120]']");
  var $formText_ZhengXiuFei = $formCheck.find("input[name='form[field_4130]']");

  //非先锋太盟移除车龄调整等表单字段
  var removeFormNotXFTM = function() {
    $formText_GouZhiShui.parents("dl").remove();
    $formText_GouRuJia.parents("dl").remove();
    $formText_ChengXinLv.parents("dl").remove();
    $formText_CheLingTiaoZheng.parents("dl").remove();
    $formText_ShiChangLengRe.parents("dl").remove();
    $formText_GengXinHuanDai.parents("dl").remove();
    $formText_CheLiangBanBenXiShu.parents("dl").remove();
    $formText_GongLiXiShu.parents("dl").remove();
    $formText_CheKuangDengJi.parents("dl").remove();
    $formText_YanSeXiShu.parents("dl").remove();
    $formText_ZhengXiuFei.parents("dl").remove();
  }

  //隐藏品牌id, 车系id，车型id，竞价次数，平均价，历史拍卖价格，事故车图片key，text html
  var hiddenBrandId = $("#formCheck input[name='form[field_2011]']");
  var hiddenSeriesId = $("#formCheck input[name='form[field_2021]']");
  var hiddenModelId = $("#formCheck input[name='form[field_2031]']");
  var hiddenBiddingCount = $("#formCheck input[name='form[field_2041]']");
  var hiddenAveragePrice = $("#formCheck input[name='form[field_2051]']");
  var hiddenHistoryPrices = $("#formCheck input[name='form[field_2061]']");
  var hiddenAccidentImg = $("#formCheck input[name='form[field_2071]']");
  var hiddenGuidancePrices = $("#formCheck input[name='form[field_2081]']");

  hiddenBrandId.parent().parent().hide();
  hiddenSeriesId.parent().parent().hide();
  hiddenModelId.parent().parent().hide();
  hiddenBiddingCount.parent().parent().hide();
  hiddenAveragePrice.parent().parent().hide();
  hiddenHistoryPrices.parent().parent().hide();
  hiddenAccidentImg.parent().parent().hide();
  hiddenGuidancePrices.parent().parent().hide();


  if (!twigObject.isXFTM) {
    removeFormNotXFTM();
  }

  if (!twigObject.showPurchasePrice) {
    $formText_ShouGouJia.parents("dl").remove();
  }

  if (!twigObject.showSellPrice) {
    $formText_XiaoShouJia.parents("dl").remove();
  }

  //如果是美车堂或车置宝开启
  if (!twigObject.isMct && !twigObject.isCzb) {
    $formText_WeilaiJia.parents("dl").remove();
  }

  if (twigObject.isManager != '1') {
      $formText_dsf.prop({
        readonly: true,
      })
  }

  //其它jquery对象
  var $tabBox = $("#checkNavTabs");
  var $tabItem = $tabBox.find("li");
  var $tabPage = $(".step-btn-footer span:not('.btn-submit')");
  var $dlTag = $formCheck.find(".form-check-dl");
  var $dtTag = $dlTag.find("dt");
  var $ddTag = $dlTag.find("dd");
  var $textType = $dlTag.find("dd.textType");
  var $textareaType = $dlTag.find("dd.textareaType");
  var $radioType = $dlTag.find("dd.radioType");
  var $checkType = $dlTag.find("dd.checkType").filter(function() {
    return !$formCheck_ShiFouPingXing.parents(".checkType").is($(this));
  });
  var $ulTagSpreadOrRetract = $ddTag.find("ul").filter(function() {
    return $(this).data(STR_DATA_NUM_ROWS);
  })
  var $ddCheckType_ShiFouPingXing = $formCheck_ShiFouPingXing.parents("dd.checkType");
  var $ddCheckTypeKongtiao = $formCheck_KongTiao.parents("dd.checkType");
  var $date = $textType.find(".date input");
  var $number = $formCheck.find("input.number");
  var $numberTotle = $number.filter(".totle");
  var $upper = $formCheck.find("input.upper");
  var $searchMaintainRecord = $('#searchMaintainRecord');
  var $searchLSJRecord = $('#searchLSJRecord');
  var $nowVIN = $("#nowVIN");
  var $nowEngine = $("#nowENGINE");
  var $interval = $(".item-interval");
  //估价模块
  var $appraisal = $(".step-appraisal");
  var $appraisalYYC = $("#yyc-auction");
  var $appraisalRadioType = $appraisal.find("dd.radioType");

  var $appraisalBtnGroup = $appraisal.find(".step-appraisal-btn");
  var $appraisalItem = $appraisal.find(".step-appraisal-item");
  var $appraisalSystem = $appraisal.find(".step-appraisal-system");

  var $appraisalSelect_300_PinPai = $appraisal.find("select[name='evalform[evalBrand]']");
  var $appraisalSelect_300_CheXi = $appraisal.find("select[name='evalform[evalSeries]']");
  var $appraisalSelect_300_CheXing = $appraisal.find("select[name='evalform[evalModel]']");
  var $appraisalRadio_300_WaiGuan = $appraisal.find("input[name='evalform[surface]']");
  var $appraisalRadio_300_NeiShi = $appraisal.find("input[name='evalform[interior]']");
  var $appraisalRadio_300_GongKuang = $appraisal.find("input[name='evalform[work_state]']");
  var $appraisalText_300_ChengShi = $appraisal.find("input[name='evalform[evalCity]']");
  var $appraisalSubmit_300 = $appraisal.find(".btn-che-300");

  var $appraisalSelect_first_PinPai = $appraisal.find(".che-first-pinpai");
  var $appraisalSelect_first_CheXi = $appraisal.find(".che-first-chexi");
  var $appraisalSelect_first_NianFen = $appraisal.find(".che-first-nianfen");
  var $appraisalSelect_first_CheXing = $appraisal.find(".che-first-chexing");

  var $appraisalSubmit_first = $appraisal.find(".btn-che-first");
  var $sameVinReportArea = $checkHeaderFix.find(".notice-area");
  var $sameVinReportIcon = $sameVinReportArea.find(".icon-notice");
  var $sameVinReportBox = $(".same-vin-report");
  var $sameVinReportContent = $sameVinReportBox.find(".popup-content");

  var $transactionData = $('#deal');
  //车型库
  var vehicleModelFilter;


  /*
   *辅助
   *辅助辅助
   *辅助辅助辅助
   *辅助辅助辅助辅助
   *辅助辅助辅助辅助辅助
   *辅助辅助辅助辅助辅助辅助
   *辅助辅助辅助辅助辅助辅助辅助
   *辅助辅助辅助辅助辅助辅助辅助辅助
   **/

  //辅助
  var assistant = {
    //折行
    lineWrap: function($element, $parents, length) {
      var text = $element.text();
      if (text == "表显里程(km)") {
        return;
      }
      if (text.length > length) {
        $parents.addClass(STR_SMALL_STYLE);
        if (text.length == length + 1) {
          $parents.addClass(STR_ONLY_ROW);
        } else {
          if (text == "手工调整指导价(元)") {
            length = length + 1;
          }
          $element.html(text.substring(0, length) + "<br/>" + text.substring(length));
        }
      }
    },

    //收起展开的默认状态
    spreadOrRetractDefault: function($element, $btn, row) {
      var chooseIndex = $element.find("." + STR_CHOOSE).last().index();
      var $parent = $element.parent();
      if ($parent.hasClass(STR_RETRACT)) {
        return;
      }
      if (chooseIndex + 2 > NUM_CONDITION_ROW_VALUE * row) {
        $parent.addClass(STR_RETRACT);
        $btn.text(STR_RETRACT_TEXT);
      } else {
        $parent.addClass(STR_SPREAD);
      }
    },

    //输入框非法字符替换
    replaceValue: function($el, reg, replaceTxt, is$1) {
      var value = $el.val();
      var replaceFn, index;
      if (is$1) {
        replaceFn = function(word, $1, i) {
          index = i + 1;
          return $1;
        };
      } else {
        replaceFn = function(word, i) {
          index = replaceTxt ? i + 1 : i;
          if ((typeof replaceTxt) == "string") {
            return replaceTxt;
          }
          return replaceTxt(word);
        };
      }
      if (reg.test(value)) {
        $el.val(value.replace(reg, replaceFn));
        this.inputMoveStart($el[0], index);
      }
    },

    //移动输入框指针
    inputMoveStart: function(dom, start) {
      if (dom.setSelectionRange) {
        dom.setSelectionRange(start, start);
      } else {
        var n = dom.createTextRange();
        n.moveStart("character", start);
        n.collapse(!0);
        n.select();
      }
    },

    //vin校验
    vinCheck: function(vin) {
      var letters = {
        A: 1,
        B: 2,
        C: 3,
        D: 4,
        E: 5,
        F: 6,
        G: 7,
        H: 8,
        J: 1,
        K: 2,
        L: 3,
        M: 4,
        N: 5,
        P: 7,
        R: 9,
        S: 2,
        T: 3,
        U: 4,
        V: 5,
        W: 6,
        X: 7,
        Y: 8,
        Z: 9
      };
      var excluded = ["I", "O", "Q"];
      var value = [8, 7, 6, 5, 4, 3, 2, 10, null, 9, 8, 7, 6, 5, 4, 3, 2];
      var number = 0;
      var reg = /\d/;
      var charAt8 = parseInt(vin.charAt(8));
      if (isNaN(charAt8)) {
        charAt8 = 10;
      }
      vin = vin.toLocaleUpperCase();

      for (var i = 0; i < excluded.length; i++) {
        if (vin.indexOf(excluded[i]) > -1) {
          return false;
        }
      }

      for (var i = 0; i < vin.length; i++) {
        if (i == 8) {
          continue;
        }

        if (/\d/.test(vin.charAt(i))) {
          number += (parseInt(vin.charAt(i)) * value[i]);
        } else if (/[A-Za-z]/.test()) {
          number += (letters[vin.charAt(i)] * value[i]);
        }
      }
      if (number % 11 != charAt8) {
        return false;
      }

      return true;
    },
    //倒计时

    interval: function(interval, minute) {
      var intervalText = "";
      var show = function(min) {
        var allH = parseInt(min / 60);
        var m = min % 60;
        var d = parseInt(allH / 24);
        var h = allH % 24;
        if (d) {
          intervalText = d + "天" + h + "时" + m + "分";
        } else {
          intervalText = h ? (h + "时" + m + "分") : m + "分";
        }
      }
      if (interval.invert == false) {
        show(minute);
      } else {
        if (interval.y || interval.m || interval.d) {
          intervalText += interval.y ? interval.y + "年" : "";
          intervalText += interval.m ? interval.m + "月" : "";
          intervalText += interval.d ? interval.d + "天" : "";
        } else {
          show(minute);
        }
      }
      $interval.find(".t1").text(intervalText);
      $interval.find(".t2").text(interval.invert ? "还剩余" : "超时");
    },
    //验证表单
    validate: function($text, $radio, $textarea) {
      var self = this;
      var result = true;
      var _$radioType = $radio || $radioType;
      var _$textType = $text || $textType;
      var _$textareaType = $textarea || $textareaType;
      var checkValidate = [$formCheck_KongTiao, $formCheck_ZuoYi];
      var isIgnore = $text ? true : false;
      var number = 0;

      _$radioType.each(function() {
        var name = $(this).find("input").eq(0).attr("name");
        var $input = $('input:radio[name="' + name + '"]:checked');
        var value = $input.val();
        var attachedValue = $.trim($(this).find(STR_ATTACHED + " input").val());
        var $textarea = $(this).find("textarea");
        var textareaValue = $.trim($textarea.val());

        var textareaBool = !!($textarea.length && !textareaValue && $input.is($(this).find("input:radio:last")));
        //componet组件跳过非空验证
        if(!$(this).find("input").is(".component")) {
          if (!value || (value == STR_OTHER && !attachedValue) || textareaBool) {
            result = self.addTip($(this), isIgnore);
          } else {
            number++;
          }
        }
      })

      _$textType.each(function() {
        var $input = $(this).find("input[type='text']");
        var value = $.trim($input.val());
        if ($input.attr("readonly") || $input.hasClass("allow-empty")) {
          return;
        }
        if (!value) {
          if ($(this).is($formText_ZhuCeRiQi.parents("dd")) && $(this).find("." + STR_CHOOSE).length) {
            return;
          }
          result = self.addTip($(this), isIgnore);
        } else {
          if ($input.parent().hasClass('date') && !REG_DATE.test(value)) {
            result = self.addTip($(this), isIgnore);
          }
          number++;
        }
      })

      _$textareaType.each(function() {
        var $textarea = $(this).find("textarea");
        var value = $.trim($textarea.val());
        if ($textarea.is($formTextarea_GuoHuXiangXi) && $formRadio_GuoHuCiShu.first()[0].checked) {
          return;
        }
        if (!value) {
          result = self.addTip($(this), isIgnore);
        } else {
          number++;
        }
      })

      if ($text && (this.saveStatus.stage != 4 && this.saveStatus.stage != 5)) {
        return {
          "number": number,
          "result": result
        };
      }

      $.each(checkValidate, function() {
        var value = this.map(function() {
          return $(this)[0].checked ? $(this).val() : "";
        }).get().join("");
        if (!value) {
          result = self.addTip(this, isIgnore);
        } else {
          number++;
        }
      })

      if ($text) {
        return {
          "number": number,
          "result": result
        };
      } else {
        return result;
      }
    },

    //增加感叹号提示
    addTip: function($el, isIgnore) {
      var index = $el.parents("." + STR_TAB_PANE).index();
      if (!isIgnore) {
        $el.parents("dl").addClass(STR_TIP);
        $tabItem.eq(index).addClass(STR_TIP);
      }
      result = false;
    },

    //去除感叹号提示
    removeTip: function($el) {
      var $parents = $el.parents("dl");
      var index = $el.parents("." + STR_TAB_PANE).index();
      $parents.removeClass(STR_TIP);
      if (!$parents.siblings('.' + STR_TIP).length) {
        $tabItem.eq(index).removeClass(STR_TIP);
      }
    },

    //车龄调整，更改以下选项触发
    //手工调整估价、购入价(手工调整指导价触发更改)、成新率(出厂日期触发更改)、
    //市场冷热、更新换代、车辆版本系数
    //公里系数、车况等级、颜色系数、整修费
    adjustYear: function() {

      var VALUE_GouRuJia, VALUE_GouRuJia, VALUE_TiaoZhengGuJia, totle = 0,
        rate, sub;

      if (!twigObject.isXFTM) {
        return;
      }

      VALUE_GouRuJia = $formText_GouRuJia.val();
      VALUE_TiaoZhengGuJia = $formText_XiaoShouJia.val();

      rate = Math.round((VALUE_GouRuJia - VALUE_TiaoZhengGuJia) / VALUE_GouRuJia * 100);

      if (!VALUE_GouRuJia || !VALUE_TiaoZhengGuJia || VALUE_GouRuJia == 0 || VALUE_TiaoZhengGuJia == 0) {
        $formText_CheLingTiaoZheng.removeClass(STR_INPUT_RED).val("");
        return;
      }

      $numberTotle.each(function() {
        var value = $(this).val().replace('%', '') || 0;
        // 特殊的处理：整修费要换算成%的形式再计算
        if ($(this).is($formText_ZhengXiuFei)) {
          value = Math.round(value / VALUE_GouRuJia * 100);
        }
        totle += parseInt(value);
      })

      sub = parseInt(rate - totle);
      $formText_CheLingTiaoZheng.toggleClass(STR_INPUT_RED, sub < 0).val(sub + "%");
    },

    //发送查询维保请求

    sendMaintainRequest: function(index, value, engine) {
      var self = this;
      var maintainStatus = this.maintainStatus;
      maintainStatus.isQuery = true;
      maintainStatus.hadResult = false;
      $.ajax({
        url: twigObject.pathSearchMaintainRequest[index], //check-footer.block.twig定义的全局变量,
        type: 'POST',
        dataType: 'json',
        data: {
          'vin': value,
          "engine": engine,
          "origins": 2
        },
        success: function(ret) {
          if (!ret.success) {
            self.autoCloseTip(ret.msg);
          }
          self.updateMaintainRecord(1, value, function() {
            maintainStatus.timer = setInterval(function() {
              self.updateMaintainRecord(1, value);
            }, NUM_MAINTAIN_TIME);
          });
        }
      });
    },

    //更新维修记录(type:1不更新数据库，2更新数据库)
    updateMaintainRecord: function(type, value, cb) {
      var maintainStatus = this.maintainStatus;
      $.ajax({
        url: twigObject.pathSearchMaintainRecord,
        data: {
          'vin': value,
          'maintain_id': twigObject.id,
          'type': type
        },
        success: function(data) {
          var oldLength = $searchMaintainRecord.find("tbody tr").length;
          if (type == 2) {
            cb && cb(data);
            return;
          }
          if (!maintainStatus.hadResult && data.length) {
            if (data[0].status != 0) {
              clearInterval(maintainStatus.timer);
              maintainStatus.hadResult = true;
              //成功或失败皆显示小红点
              $tabItem.filter("[data-tabtitle='cwb']").addClass(STR_TIP);
              data[0].status == 2 && (maintainStatus.isQuery = false);
            } else {
              cb && cb();
            }
          }

          $searchMaintainRecord.bootstrapTable('destroy');
          if (twigObject.orderStatus == 3) {
            $searchMaintainRecord.bootstrapTable({
              classes: "table table-no-bordered",
              formatNoMatches: function() {
                return "抱歉，暂无查询记录"
              },

              columns: [{
                  field: 'supplierType',
                  title: '查询来源',
                  formatter: function(value, row, index) {
                    var str = ['大圣来了', '车鉴定', '查博士', '聚合数据', '蚂蚁女王'];
                    return str[value - 1] || str[0];
                  }
                },
                {
                  field: 'createdAt',
                  title: '查询时间',
                  formatter: function(value, row, index) {
                    var strArr = value.split(" ");
                    var str = strArr[1] ? (strArr[0] + "<br />" + strArr[1]) : strArr[0];
                    return str;
                  }
                },
                {
                  field: 'status',
                  title: '查询状态',
                  formatter: function(value, row, index) {
                    var str = ['查询中', '查询成功', '查询失败'];
                    return str[value];
                  }
                },
                {
                  field: 'status',
                  title: '操作',
                  formatter: function(value, row, index) {
                    var url = twigObject.urlMaintainRecordDetail;
                    url = url.replace("maintain_id", row.id);
                    url = url.replace("report_id", twigObject.id);

                    if (1 === row.status) {
                      return '<a class="maintain-detail-btn" href="' + url + '"target="_blank">查看详情</a>';
                    } else {
                      return '<span class="maintain-detail-btn disabled">查看详情</span>';
                    }
                  }
                }
              ],
              data: data
            });
          } else {
            $searchMaintainRecord.bootstrapTable({
              classes: "table table-no-bordered",
              formatNoMatches: function() {
                return "抱歉，暂无查询记录"
              },

              columns: [{
                  field: 'operator',
                  title: '查询人'
                },
                {
                  field: 'supplierType',
                  title: '查询来源',
                  formatter: function(value, row, index) {
                    var str = ['大圣来了', '车鉴定', '查博士', '聚合数据', '蚂蚁女王'];
                    return str[value - 1] || str[0];
                  }
                },
                {
                  field: 'createdAt',
                  title: '查询时间',
                  formatter: function(value, row, index) {
                    var strArr = value.split(" ");
                    var str = strArr[1] ? (strArr[0] + "<br />" + strArr[1]) : strArr[0];
                    return str;
                  }
                },
                {
                  field: 'status',
                  title: '查询状态',
                  formatter: function(value, row, index) {
                    var str = ['查询中', '查询成功', '查询失败'];
                    return str[value];
                  }
                },
                {
                  field: 'status',
                  title: '操作',
                  formatter: function(value, row, index) {
                    var url = twigObject.urlMaintainRecordDetail;
                    url = url.replace("maintain_id", row.id);
                    url = url.replace("report_id", twigObject.id);

                    if (1 === row.status) {
                      return '<a class="maintain-detail-btn" href="' + url + '"target="_blank">查看详情</a>';
                    } else {
                      return '<span class="maintain-detail-btn disabled">查看详情</span>';
                    }
                  }
                }
              ],
              data: data
            });
          }
        }
      });
    },

    //发送查询老司机请求
    sendLSJRequest: function(vin, engineNumber, licence) {
      var self = this;
      var lsjStatus = this.lsjStatus;
      lsjStatus.isQuery = true;
      lsjStatus.hadResult = false;
      $.ajax({
        url: twigObject.pathSearchLSJRequest, //check.html.twig定义的全局变量,
        dataType: 'json',
        data: {
          'licence': licence,
          'vin': vin,
          "engineNumber": engineNumber,
          'orderNo': twigObject.orderNo
        },
        success: function(ret) {
          if (!ret.success) {
            self.autoCloseTip(ret.msg);
          }
          self.updateLSJRecord(vin, function() {
            lsjStatus.timer = setInterval(function() {
              self.updateLSJRecord(vin);
            }, NUM_MAINTAIN_TIME);
          });
        }
      });
    },

    //更新老司机记录
    updateLSJRecord: function(vin, cb) {
      var lsjStatus = this.lsjStatus;
      $.ajax({
        url: twigObject.pathSearchLSJRecord,
        data: {
          'vin': vin
        },
        dataType: 'json',
        success: function(data) {
          var oldLength = $searchLSJRecord.find("tbody tr").length;

          if (!lsjStatus.hadResult && data.length) {
            if (data[0].status != 0) {
              clearInterval(lsjStatus.timer);
              lsjStatus.hadResult = true;
              //成功或失败皆显示小红点
              $tabItem.filter("[data-tabtitle='cwb']").addClass(STR_TIP);
              data[0].status == 2 && (lsjStatus.isQuery = false);
            } else {
              cb && cb();
            }
          }

          $searchLSJRecord.bootstrapTable('destroy');
          if (twigObject.orderStatus == 3) {
            $searchLSJRecord.bootstrapTable({
              classes: "table table-no-bordered",
              formatNoMatches: function() {
                return "抱歉，暂无查询记录"
              },

              columns: [{
                  field: 'createdAt',
                  title: '查询时间',
                  formatter: function(value, row, index) {
                    var strArr = value.split(" ");
                    var str = strArr[1] ? (strArr[0] + "<br />" + strArr[1]) : strArr[0];
                    return str;
                  }
                },
                {
                  field: 'status',
                  title: '状态',
                  formatter: function(value, row, index) {
                    if (0 === value) {
                      return '查询中';
                    } else if (1 === value) {
                      return '查询成功';
                    } else if (2 === value) {
                      return '查询失败';
                    }
                  }
                },
                {
                  field: 'supplierType',
                  title: '数据来源',
                  formatter: function(value, row, index) {
                    if (1 === value) {
                      return '老司机';
                    } else {
                      return '';
                    }
                  }
                },
                {
                  field: 'operate',
                  title: '操作',
                  align: 'center',
                  formatter: function(value, row, index) {
                    var url = twigObject.urlLSJRecordDetail;
                    url = url.replace("insuranceId", row.id);

                    if (1 === row.status) {
                      return '<a class="maintain-detail-btn" href="' + url + '"target="_blank">查看详情</a>';
                    } else {
                      return '<span class="maintain-detail-btn disabled">查看详情</span>';
                    }
                  }
                }
              ],
              data: data
            });
          } else {
            $searchLSJRecord.bootstrapTable({
              classes: "table table-no-bordered",
              formatNoMatches: function() {
                return "抱歉，暂无查询记录"
              },

              columns: [{
                  field: 'operator',
                  title: '查询人'
                },
                {
                  field: 'createdAt',
                  title: '查询时间',
                  formatter: function(value, row, index) {
                    var strArr = value.split(" ");
                    var str = strArr[1] ? (strArr[0] + "<br />" + strArr[1]) : strArr[0];
                    return str;
                  }
                },
                {
                  field: 'status',
                  title: '状态',
                  formatter: function(value, row, index) {
                    if (0 === value) {
                      return '查询中';
                    } else if (1 === value) {
                      return '查询成功';
                    } else if (2 === value) {
                      return '查询失败';
                    }
                  }
                },
                {
                  field: 'supplierType',
                  title: '数据来源',
                  formatter: function(value, row, index) {
                    if (1 === value) {
                      return '老司机';
                    } else {
                      return '';
                    }
                  }
                },
                {
                  field: 'operate',
                  title: '操作',
                  align: 'center',
                  formatter: function(value, row, index) {
                    var url = twigObject.urlLSJRecordDetail;
                    url = url.replace("insuranceId", row.id);

                    if (1 === row.status) {
                      return '<a class="maintain-detail-btn" href="' + url + '"target="_blank">查看详情</a>';
                    } else {
                      return '<span class="maintain-detail-btn disabled">查看详情</span>';
                    }
                  }
                }
              ],
              data: data
            });
          }
        }
      });
    },

    //相同vin码历史报告
    sameVinReport: function(vin, isShow) {
      var self = this;
      $.ajax({
        url: twigObject.pathGetReportsByVin,
        data: {
          'vin': vin
        },
        success: function(data) {
          var url = twigObject.pathReport;
          var tipHTML = '<div class="vin-tip">当前 <span class="ft-red1">VIN:' + vin + '</span> 在系统中已经存在，以下是历史记录：</div>';
          var listHTML = '';
          var twoPrice = false;
          if (data.success) {
            self.sameVinReportList = data.results;
            listHTML = $.map(data.results, function(n, i) {
              var status = n.status == 1 ? "评估通过" : "评估拒绝";
              var url = twigObject.pathReport.replace("orderid", n.orderId);
              var price4010 = (n.report.field_4010 && n.report.field_4010.value) ? ("收购价" + (n.report.field_4010.value / 10000).toFixed(2) + "万") : "",
                price4012 = (n.report.field_4012 && n.report.field_4012.value) ? ("销售价" + (n.report.field_4012.value / 10000).toFixed(2) + "万") : "",
                time = "",
                itemHTML,
                separator = (price4010 && price4012) ? "\/" : "";
              if (separator) {
                twoPrice = true;
              }
              if (n.examedAt) {
                time = n.examedAt.date.substring(0, 4) + "年" +
                  parseInt(n.examedAt.date.substring(5, 7)) + "月" +
                  parseInt(n.examedAt.date.substring(8, 10)) + "日";
              }
              itemHTML = '<li class="clearfix">' +
                '<div class="sub-item time">' + (i + 1) + '、' + time + '</div>' +
                '<div class="sub-item status">' + status + '</div>' +
                '<div class="sub-item price">' + price4010 + separator + price4012 + '</div>' +
                '<div class="sub-item btn-group-specific">' +
                '<a class="btn btn-default" href="' + url + '" target="_blank">查看报告</a>' +
                '<span class="btn btn-danger use-this-appraisal">使用该数据</span>' +
                '</div>'
              '</li>'
              return itemHTML;
            }).join("");
            listHTML = "<ul>" + listHTML + "</ul>";
            $sameVinReportContent.html(tipHTML + listHTML);
            twoPrice && $sameVinReportContent.addClass('two-price');
            $sameVinReportArea.show();
            isShow && $checkHeaderFix.addClass(STR_SHOW_POPUP);
          }
        }
      })
    },

    //自动关闭的提示信息
    autoCloseTip: function(message) {
      commonMethod.showTopTips(message); //调用default.js内的通用方法
    },

    //又一车估价检测更新
    appraisalYYC: function() {
      var valuePinPai = $.trim($formText_PinPai.val());
      var valueCheXi = $.trim($formText_CheXi.val());
      var valueCheXing = $.trim($formText_CheXing.val());
      var valueNianKuan = $.trim($formText_NianKuan.val());
      var valueModelId = $.trim($formText_ModelId.val());
      var valueGuidancePrices = $.trim(hiddenGuidancePrices.val());
      var valueChengShi = $("#cUc").text();
      var valueLiCheng = $.trim($formText_BiaoXianLiCheng.val());
      var valueShangPai = $.trim($formText_ShangPaiShiJian.val());

      var $itemYYC = $appraisalItem.filter(".item-yyc");
      var isChangeFn = function(oldValue, newValue) {
        if (oldValue != newValue) {
          _isChange = true;
        }
      }

      $itemYYC.find("input.pinpai").val(function() {
        isChangeFn($(this).val(), valuePinPai);
        return valuePinPai;
      });
      $itemYYC.find("input.chexi").val(function() {
        isChangeFn($(this).val(), valueCheXi);
        return valueCheXi;
      });
      $itemYYC.find("input.chexing").val(function() {
        isChangeFn($(this).val(), valueCheXing);
        return valueCheXing;
      });
      $itemYYC.find("input.niankuan").val(function() {
        isChangeFn($(this).val(), valueNianKuan);
        return valueNianKuan;
      });
      $itemYYC.find("input.zhidaojia").val(function() {
        isChangeFn($(this).val(), valueGuidancePrices);
        return valueGuidancePrices;
      });
      $itemYYC.find("input.licheng").val(function() {
        isChangeFn($(this).val(), valueLiCheng);
        return valueLiCheng;
      });
      $itemYYC.find("input.shangpai").val(function() {
        isChangeFn($(this).val(), valueShangPai);
        return valueShangPai;
      });
      assistant.appraisalUpdateYYC();
			  _isChange = true;
    },

    //又一车估价更新
    appraisalUpdateYYC: function() {
      var valuePinPai = $.trim($formText_PinPai.val());
      var valueCheXi = $.trim($formText_CheXi.val());
      var valueCheXing = $.trim($formText_CheXing.val());
      var valueNianKuan = $.trim($formText_NianKuan.val());
      var valueModelId = $.trim($formText_ModelId.val());
      var valueChengShi = $("#cUc").text();
      var valueLiCheng = $.trim($formText_BiaoXianLiCheng.val());
      var valueShangPai = $.trim($formText_ShangPaiShiJian.val());
      var $itemYYC = $appraisalItem.filter(".item-yyc");
      var url = twigObject.pathEvalPriceYYC;
      var href, hrefArr;
      if (valuePinPai != "" && valueCheXi != "" && valueCheXing != "" && valueNianKuan != "" && typeof(_isChange) != "undefined") {
        if (_isChange) {
          url = url.replace("mId", valueModelId);
          url = url.replace("ct", valueChengShi);
          url = url.replace("bv", valuePinPai);
          url = url.replace("sv", valueCheXi);
          url = url.replace("mv", valueCheXing);
          url = url.replace("yv", valueNianKuan);
          href = url + "&type=2";
          $('.panel-price').hide();
          $("#btn-yyc-price").attr("href", href);
        }
      }
    },

    //北极星系统估价
    appraisalSystem: function() {
			_isChange = false;
      var valuePinPai = $.trim($formText_PinPai.val());
      var valueCheXi = $.trim($formText_CheXi.val());
      var valueCheXing = $.trim($formText_CheXing.val());
      var valueNianKuan = $.trim($formText_NianKuan.val());
      var valueModelId = $.trim($formText_ModelId.val());
      var valueChengShi = $("#cUc").text();
      var valueLiCheng = $.trim($formText_BiaoXianLiCheng.val());
      var valueZhuCeRiQi = $.trim($formText_ZhuCeRiQi.val());
      var $itemYYC = $appraisalItem.filter(".item-yyc");
      if (valuePinPai != "" && valueCheXi != "" && valueCheXing != "" && valueNianKuan != "" && valueLiCheng != "" && typeof(_isChange) != "undefined") {
          var _url = twigObject.pathAppraisalBeiJiXin;
          var _reportID = twigObject.reportID;
          $.ajax({
            type: "get",
            data: {
              "modelId": valueModelId,
              "registerDate": valueZhuCeRiQi,
              "kilometer": valueLiCheng,
              "city": valueChengShi,
              "reportID": _reportID
            },
            dataType: "json",
            url: _url,
            success: function(data) {
              $('.panel-price').show();
              if (data.success == true) {
                $(".step-appraisal-result_new").show();
                var currentRate = data.data.current.price;
                var oneYearRate = data.data.one_year.price;
                var currentRank = data.data.current.accuracy;
                var oneYearRank = data.data.one_year.accuracy;
                currentRate = parseInt(currentRate * 10000);
                oneYearRate = parseInt(oneYearRate * 10000);
                $('.fail').hide();
                $("#currentPricePoint").popover({
                  trigger: 'hover',
                  placement: 'top',
                  content: data.data.current.model
                });
                $("#oneYearPoint").popover({
                  trigger: 'hover',
                  placement: 'bottom',
                  content: data.data.one_year.model
                });
                $('.deal-list').show()
                $(".yyc-bjx-price").show().find('.present-price').text(currentRate);
                $(".yyc-bjx-oneyear").show().find('.oneyear-price').text(oneYearRate);
                if (currentRank == 1) {
                  $(".currentRating-wrap").css('background-position-y', '-3px')
                } else if (currentRank == 2) {
                  $(".currentRating-wrap").css('background-position-y', '-27px')
                } else if (currentRank == 3) {
                  $(".currentRating-wrap").css('background-position-y', '-49px')
                } else if (currentRank == 4) {
                  $(".currentRating-wrap").css('background-position-y', '-72px')
                };
                if (oneYearRank == 1) {
                  $(".oneYearRating-wrap").css('background-position-y', '-3px')
                } else if (oneYearRank == 2) {
                  $(".oneYearRating-wrap").css('background-position-y', '-27px')
                } else if (oneYearRank == 3) {
                  $(".oneYearRating-wrap").css('background-position-y', '-49px')
                } else if (oneYearRank == 4) {
                  $(".oneYearRating-wrap").css('background-position-y', '-72px')
                };
                $transactionData.bootstrapTable('destroy');
                $transactionData.bootstrapTable({
                  classes: "table table-no-bordered",
                  formatNoMatches: function() {
                    return "抱歉，暂无成交数据"
                  },
                  columns: [{
                      field: 'dealAt',
                      title: '成交时间',
                      formatter: function(value, row, index) {
                        return value;
                      }
                    },
                    {
                      field: 'carAge',
                      title: '车龄',
                      formatter: function(value, row, index) {
                        return value;
                      }
                    },
                    {
                      field: 'mileage',
                      title: '公里数(万)',
                      formatter: function(value, row, index) {
                        return value;
                      }
                    },
                    {
                      field: 'city',
                      title: '城市',
                      formatter: function(value, row, index) {
                        return value;
                      }
                    },
                    {
                      field: 'price',
                      title: '销售价',
                      formatter: function(value, row, index) {
                        return value;
                      }
                    },
                    // {
                    //     field: 'type',
                    //     title: '类型',
                    //     formatter: function(value, row, index){
                    //     	return value;
                    //     }
                    // },
                  ],
                  data: data.data.deals
                });
              } else if (data.success == false) {
                $('.fail').show().text(data.errmsg);
                $(".yyc-bjx-price").hide();
                $(".yyc-bjx-oneyear").hide();
              } else {
                $('.fail').show().text(data.errmsg);
                $(".yyc-bjx-price").hide();
                $(".yyc-bjx-oneyear").hide();
                $(".deal-list").hide();

              }
            },
            error: function() {
              $('.panel-price').show();
              $(".step-appraisal-result_new").hide();
              $('.fail').show().text("网络错误，请稍后再试！");
            }
          })
      }else{
				$('.panel-price').show();
				$('.fail').show().text(STR_MESSAGE_VALIDATE+"，系统无法估价")
			}
    },

    //确认车型
    modelConfirm: function(data, common) {
      var detail = this.modelDetails = data.data;
      this.modelDetails.commonBrand = ["品牌", common.brand];
      this.modelDetails.commonSeries = ["车系", common.series];
      dataBackfill.textBackfill($formText_PinPai, {
        'value': common.brand
      });
      dataBackfill.textBackfill($formText_CheXi, {
        'value': common.series
      });
      dataBackfill.textBackfill($formText_CheXing, {
        'value': detail["7a0bca72bdaf5d8d"][1]
      });
      dataBackfill.textBackfill($formText_NianKuan, {
        'value': detail["seriesyear"][1]
      });

      if (typeof detail["body_structure"] != "undefined") {
        dataBackfill.radioBackfill($formRadio_CheLiangLeiXing, detail["body_structure"][1]);
      }

      if (typeof detail["engine_env"] != "undefined") {
        dataBackfill.radioBackfill($formRadio_HuanBaoBiaoZhun, detail["engine_env"][1]);
      }
      if (typeof detail["gear_type"] != "undefined") {
        dataBackfill.radioBackfill($formRadio_BianSuXingShi, detail["gear_type"][1]);
      }
      if (typeof detail["body_door"] != "undefined") {
        dataBackfill.radioBackfill($formRadio_CheMenShu, detail["body_door"][1]);
      }
      if (typeof detail["chassis_drive"] != "undefined") {
        dataBackfill.radioBackfill($formRadio_QuDongXingShi, detail["chassis_drive"][1]);
      }
      if (typeof detail["engine_onflow"] != "undefined") {
        dataBackfill.radioBackfill($formRadio_JinQiFangShi, detail["engine_onflow"][1]);
      }

      external_full = typeof detail["external_full"] == "undefined" ? false : detail["external_full"][1];
      external_skylight = typeof detail["external_skylight"] == "undefined" ? false : detail["external_skylight"][1];
      dataBackfill.radioTianChuangBackfill(external_full, external_skylight);

      seat_material = typeof detail["seat_material"] == "undefined" ? false : detail["seat_material"][1];
      seat_mmotor = typeof detail["seat_mmotor"] == "undefined" ? false : detail["seat_mmotor"][1];
      seat_smotor = typeof detail["seat_smotor"] == "undefined" ? false : detail["seat_smotor"][1];
      seat_bmotor = typeof detail["seat_bmotor"] == "undefined" ? false : detail["seat_bmotor"][1];
      seat_fheating = typeof detail["seat_fheating"] == "undefined" ? false : detail["seat_fheating"][1];
      seat_bheating = typeof detail["seat_bheating"] == "undefined" ? false : detail["seat_bheating"][1];
      seat_memory = typeof detail["seat_memory"] == "undefined" ? false : detail["seat_memory"][1];
      seat_faeration = typeof detail["seat_faeration"] == "undefined" ? false : detail["seat_faeration"][1];
      seat_baeration = typeof detail["seat_baeration"] == "undefined" ? false : detail["seat_baeration"][1];
      seat_fmassage = typeof detail["seat_fmassage"] == "undefined" ? false : detail["seat_fmassage"][1];
      seat_bmassage = typeof detail["seat_bmassage"] == "undefined" ? false : detail["seat_bmassage"][1];

      dataBackfill.checkZuoYiBackfill({
        caiZhi: seat_material,
        zhuJiaDianDong: seat_mmotor,
        fuJiaDianDong: seat_smotor,
        houPaiDianDong: seat_bmotor,
        qianPaiJiaRe: seat_fheating,
        houPaiJiaRe: seat_bheating,
        jiyi: seat_memory,
        qianPaiTongFeng: seat_faeration,
        houPaiTongFeng: seat_baeration,
        qianPaiAnMo: seat_baeration,
        houPaiAnMo: seat_bmassage,
      });

      if (typeof detail["cool_type"] != "undefined" && typeof detail["cool_alone"] != "undefined") {
        dataBackfill.checkKongTiaoBackfill(detail["cool_type"][1], detail["cool_alone"][1]);
      }

      if (typeof detail["multimedia_btv"] != "undefined") {
        dataBackfill.radioBackfillSign($formRadio_HouPaiYeJing, detail["multimedia_btv"][1]);
      }

      if (typeof detail["internal_cruise"] != "undefined") {
        dataBackfill.radioBackfillSign($formRadio_XunHang, detail["internal_cruise"][1]);
      }

      if (typeof detail["control_air"] != "undefined") {
        //空气悬架和底盘升降使用一个参数
        dataBackfill.radioBackfillSign($formRadio_KongQiXuanJia, detail["control_air"][1]);
        dataBackfill.radioBackfillSign($formRadio_DiPanShengJiang, detail["control_air"][1]);
      }
      if (typeof detail["lighting_head"] != "undefined") {
        dataBackfill.radioBackfillSign($formRadio_ZiDongDaDeng, detail["lighting_head"][1]);
      }

      if (typeof detail["glass_reaction"] != "undefined") {
        dataBackfill.radioBackfillSign($formRadio_ZiDongYuGua, detail["glass_reaction"][1]);
      }

      if (typeof detail["safe_start"] != "undefined") {
        dataBackfill.radioBackfillSignQiDong($formRadio_QiDongFangShi, detail["safe_start"][1]);
      }
    },

    setIsClickSubmit: function(bool) {
      if (twigObject.origin != "admin") {
        this.isClickSubmit = bool;
      }
    },

    //保存后的提示信息
    saveTip: function(ret, tipType, type) {
      var msg = ret.success ? STR_MESSAGE_SAVE_SUCCESS : STR_MESSAGE_VALIDATE;

      if (tipType == 0) {
        return;
      } else {
        if (type == 3) {
          msg = "解锁失败";

        }!ret.success && commonMethod.alert({
          msgHTML: msg
        });
      }
    },

    //判断审核阶段
    judgeStage: function() {
      var $text, $textarea, $radio, validate;
      switch (this.saveStatus.stage) {
        case 0:
          return 1;
        case 1:
          $text = $step1.find("dd.textType");
          $textarea = $step1.find("dd.textareaType");
          $radio = $step1.find("dd.radioType");
          validate = this.validate($text, $radio, $textarea);
          if (validate.result) {
            return 2;
          } else {
            return 1;
          }
        case 2:
          $text = $step3.find("dd.textType");
          $textarea = $step3.find("dd.textareaType");
          $radio = $step3.find("dd.radioType");
          validate = this.validate($text, $radio, $textarea);
          if (validate.result) {
            return 3;
          } else {
            return validate.number > 0 ? 3 : 2;
          }
        case 3:
          $text = $step4.find("dd.textType");
          $textarea = $step4.find("dd.textareaType");
          $radio = $step4.find("dd.radioType");
          validate = this.validate($text, $radio, $textarea);
          if (validate.result) {
            return 4;
          } else {
            return validate.number > 0 ? 3 : 2;
          }
        case 4:
          $text = $step5.find("dd.textType");
          $textarea = $step5.find("dd.textareaType");
          $radio = $step5.find("dd.radioType");
          validate = this.validate($text, $radio, $textarea);
          if (validate.result) {
            return 5;
          } else {
            return validate.number > 0 ? 4 : 3;
          }
        case 5:
          $text = $step6.find("dd.textType");
          $textarea = $step6.find("dd.textareaType");
          $radio = $step6.find("dd.radioType");
          validate = this.validate($text, $radio, $textarea);
          if (validate.result) {
            return 5;
          } else {
            return validate.number > 0 ? 5 : 4;
          }
        default:
          return this.saveStatus.stage;
      }
    },

    //跳转链接
    jumpLink: function(href) {
      $(window).off("beforeunload");
      window.location.href = href;
    },

    //事故图片处理
    accidentImgHandle: function() {
      var imgs = [];
      $("input.cb:checked").each(function(index, el) {
        var qiniu_key = $(this).parent().parent().attr('data-qiniu-key');
        imgs.push(qiniu_key);
      });

      if (imgs.length > 0) {
        hiddenAccidentImg.val(imgs.join(','));
      }
    },

    //component勾选统计事故折损率
    componentCheckedRate: function(event) {
      if($(this).children().find(".component").attr("checked") == "checked") {
        $(this).removeClass("choose");
        $(this).children().find(".component").removeAttr("checked");
        $(this).children().find(".component").val("null");
      } else {
        $(this).addClass("choose");
        $(this).children().find(".component").attr("checked", "checked");
        $(this).siblings(".choose").children().find(".component").removeAttr("checked");
        $(this).siblings(".choose").removeClass("choose");
      }
      handle.chooseRadioComponent();
    },

    //审核form保存相关状态
    saveStatus: {
      isSave: true, //是否可以执行保存操作
      stage: parseInt(twigObject.stage), //审核阶段
      serialize: "" //上一次保存的数据
    },

    //当次操作维保查询相关状态
    maintainStatus: {
      isQuery: false, //是否查询过
      hadResult: true, //结果是否返回
      timer: null //轮询定义
    },

    //当次操作老司机查询相关状态
    lsjStatus: {
      isQuery: false, //是否查询过
      hadResult: true, //结果是否返回
      timer: null //轮询定义
    },


    //第一车网品牌列表
    cheFirstPinPaiList: null,
    //相同vin历史报告
    sameVinReportList: null,

    //车型库车型详细数据
    modelDetails: null,

    //仅针对C端用户
    isClickSubmit: false
  };


  /*
   *数据回填
   *数据回填数据回填
   *数据回填数据回填
   *数据回填数据回填数据回填
   *数据回填数据回填数据回填数据回填
   *数据回填数据回填数据回填数据回填数据回填
   *数据回填数据回填数据回填数据回填数据回填数据回填
   *数据回填数据回填数据回填数据回填数据回填数据回填数据回填
   **/

  //数据回填
  var dataBackfill = {
    //输入框回填
    textBackfill: function($el, val) {
      var value = ((val && val.value) ? val.value : "") || "";
      $el.val(value).change();
    },

    //单选回填
    radioBackfill: function($el, val) {
      var value = (val && val.value) ? val.value : val;
      var $dd = $el.parents("dd");
      var $ul = $el.parents("ul");
      var $attachedInput = $ul.find(STR_ATTACHED).find("input");
      var $choose = $el.filter(function() {
        return $(this).val() == value && $(this).val() != STR_OTHER;
      });
      var $btn = $ul.next("." + STR_BTN_SPREAD_OR_RETRACT);
      var isSpread = false;
      var row = $ul.data(STR_DATA_NUM_ROWS) || 0;
      var self = this;

      if ($choose.length) {
        this.radioCancel($choose, true);
        if ($choose.parents("li").index() + 2 > NUM_CONDITION_ROW_VALUE * row) {
          isSpread = true;
        }
      } else {
        if ($attachedInput.length) {
          $attachedInput.val(value).change();
          isSpread = true;
        } else {
          $el.each(function() {
            self.radioCancel($(this), false);
          })
        }
      }

      if ($dd.hasClass(STR_SPREAD) && isSpread) {
        $btn.click();
      }
    },

    //复选数据回填(数组形式)
    checkBackfill: function($el, val) {
      var self = this;
      var list = ((val && val.value) ? val.value : val) || [];
      if (list.length > 0) {
        $el.each(function() {
          var checked = false;
          for (var i = 0; i < list.length; i++) {
            if ($(this).val() == list[i]) {
              checked = true;
            }
          }
          self.chooseStatusChange($(this), checked);
          checked && assistant.removeTip($(this));
        })
      }
    },

    //单选回填(根据●和-、○判断)
    radioBackfillSign: function($el, sign) {
      var value = sign == "●" ? "有" : "无";
      this.radioBackfill($el, value);
    },

    //单选状态更改
    radioCancel: function($el, checked) {
      if (checked) {
        $el[0].checked = true;
      //针对component组件添加class,不触发click
        if($el.is(".component")){
          $el.parents("li").addClass(STR_CHOOSE);
          $el.attr("checked", "checked");
        } else {
          $el.parents("li").click();
        }
      } else {
        $el[0].checked = false;
        $el.parents("li").removeClass(STR_CHOOSE);
      }
    },

    //启动方式回填
    radioBackfillSignQiDong: function($el, sign) {
      var value = sign == "●" ? "无钥匙启动" : "普通钥匙启动";
      this.radioBackfill($el, value);
    },

    //天窗回填
    radioTianChuangBackfill: function(value1, value2) {
      var self = this;
      if (value1 == "●") {
        this.radioCancel($formRadio_TianChuang.filter("[value='全景']"), true);
      } else if (value2 == "●") {
        this.radioCancel($formRadio_TianChuang.filter("[value='普通']"), true);
      } else {
        $formRadio_TianChuang.each(function() {
          self.radioCancel($(this), false);
        })
      }
    },

    //回填座椅
    checkZuoYiBackfill: function(options) {
      this.checkMutex($formCheck_ZuoYi.filter("[value='织物']"), $formCheck_ZuoYi.filter("[value='真皮']"), options.caiZhi)

      this.chooseStatusChange($formCheck_ZuoYi.filter("[value='手动']"), false);
      this.chooseStatusChangeSign($formCheck_ZuoYi.filter("[value='主驾电动']"), options.zhuJiaDianDong);
      this.chooseStatusChangeSign($formCheck_ZuoYi.filter("[value='副驾电动']"), options.fuJiaDianDong);
      this.chooseStatusChangeSign($formCheck_ZuoYi.filter("[value='后排电动']"), options.houPaiDianDong);
      this.chooseStatusChangeSign($formCheck_ZuoYi.filter("[value='前排加热']"), options.qianPaiJiaRe);
      this.chooseStatusChangeSign($formCheck_ZuoYi.filter("[value='后排加热']"), options.houPaiJiaRe);
      this.chooseStatusChangeSign($formCheck_ZuoYi.filter("[value='记忆']"), options.jiyi);
      this.chooseStatusChangeSignDouble($formCheck_ZuoYi.filter("[value='通风']"), options.qianPaiTongFeng, options.houPaiTongFeng);
      this.chooseStatusChangeSignDouble($formCheck_ZuoYi.filter("[value='按摩']"), options.qianPaiAnMo, options.houPaiAnMo);

    },

    //回填空调
    checkKongTiaoBackfill: function(value1, value2) {
      this.checkMutex($formCheck_KongTiao.filter("[value='手动']"), $formCheck_KongTiao.filter("[value='自动']"), value1);
      this.chooseStatusChangeSign($formCheck_KongTiao.filter("[value='后排独立空调']"), value2);
      this.chooseStatusChange($formCheck_KongTiao.filter("[value='无']"), false);
    },

    //复选(两个选项互斥)
    checkMutex: function($el_1, $el_2, currentValue) {
      if (currentValue == $el_1.val()) {
        this.chooseStatusChange($el_1, true);
        this.chooseStatusChange($el_2, false);
      } else if (currentValue == $el_2.val()) {
        this.chooseStatusChange($el_1, false);
        this.chooseStatusChange($el_2, true);
      } else {
        this.chooseStatusChange($el_1, false);
        this.chooseStatusChange($el_2, false);
      }
    },

    //选中状态更改
    chooseStatusChange: function($el, checked) {
      $el[0].checked = checked;
      $el.parents("li").toggleClass(STR_CHOOSE, checked);
      checked && assistant.removeTip($el);
    },

    //选中状态更改(根据●和-、○判断)
    chooseStatusChangeSign: function($el, sign) {
      var checked = sign == "●" ? true : false;
      this.chooseStatusChange($el, checked);
    },

    //选中状态更改两个参数(根据●和-、○判断)
    chooseStatusChangeSignDouble: function($el, value1, value2) {
      var checked = (value1 == "●" || value2 == "●") ? true : false;
      this.chooseStatusChange($el, checked);
    },

    //是否平行进口回填
    backfillShiFouPingXing: function(val) {
      var value = (val && val.value) ? val.value : "";
      var index = value ? 0 : 1;
      $ddCheckType_ShiFouPingXing.find("." + STR_BTN_CONDITION).eq(index).click();
    },

    //使用历史vin记录回填整个表单
    backfillAll: function(index) {
      var report = assistant.sameVinReportList[index].report;
      var exclude = ['field_2071', 'field_1010', 'field_1080', 'field_4010', 'field_4012', 'field_4020', 'field_4030', 'field_4040', 'field_4060', 'field_4140', 'field_4150', 'field_result'];
      var $item, name, isEC = false;

      for (var field in report) {
        for (var ec = 0; ec < exclude.length; ec++) {
          if (field == exclude[ec]) {
            isEC = true;
            break;
          }
        }
        if (isEC) {
          isEC = false;
          continue;
        }
        if (field == "field_1021") {
          this.backfillShiFouPingXing(report[field]);
          continue;
        }
        name = "form[" + field + "]";
        $item = $formCheck.find("input[name='" + name + "']" + ",input[name='" + name + "[value]']" + ",input[name='" + name + "[value][]']");
        if ($item.length) {
          switch ($item.eq(0).attr("type")) {
            case "text":
              this.textBackfill($item, report[field]);
              break;
            case "radio":
              this.radioBackfill($item, report[field]);
              break;
            case "checkbox":
              this.checkBackfill($item, report[field]);
              break;
          }
        }
      }
    }
  };

  /*
   *重构dom
   *重构dom重构dom
   *重构dom重构dom重构dom
   *重构dom重构dom重构dom重构dom
   *重构dom重构dom重构dom重构dom重构dom
   *重构dom重构dom重构dom重构dom重构dom重构dom
   *重构dom重构dom重构dom重构dom重构dom重构dom重构dom
   **/

  //为了美观和交互，且不修改数据结构，只能重构dom结构了
  var reconsitutionDom = {
    //表单项标题处理
    dtTitle: function() {
      $dtTag.each(function(index, el) {
        var $span = $(this).find("span");
        $(this).attr("title", $span.text());
        assistant.lineWrap($span, $(this), NUM_LINE_WRAP_LENGTH_2);
      })
    },

    //基本信息内添加查维保按钮
    addSearchMaintain: function() {
      var maintainHTML = '<div class="search-maintain-group clearfix">' +
        '<span class="' + STR_SEARCH_MAINTAIN_BTN + '" style="display:none">查大圣来了</span>' +
        '<span class="' + STR_SEARCH_MAINTAIN_BTN + '">查车鉴定</span>' +
        '<span class="' + STR_SEARCH_MAINTAIN_BTN + '">查查博士</span>' +
        '<span class="' + STR_SEARCH_MAINTAIN_BTN + '" style="display:none">查聚合数据</span>' +
        '<span class="' + STR_SEARCH_MAINTAIN_BTN + '">查蚂蚁女王</span>' +
        '</div>';
      $formText_Engine.after(maintainHTML);
    },

    //车龄调整为负数时更改背景色着重显示
    chelingtiaozheng: function() {
      var value = $formText_CheLingTiaoZheng.val();
      value = value.replace("%", "");
      if (parseInt(value) < 0) {
        $formText_CheLingTiaoZheng.addClass(STR_INPUT_RED);
      }
    },

    //textType加placeholder
    textType: function() {
      $textType.each(function() {
        var placeholder = "请输入" + $(this).parents("dl").find("dt").attr("title");
        var $input = $(this).find("input");
        !$input.attr("readonly") && $(this).find("input").attr("placeholder", placeholder);
      })
    },

    //默认单选项处理
    radioType: function($elements) {
      $elements.each(function(index, el) {
        var $item = $(this).find(STR_NOT_ATTACHED);
        var $attached = $(this).find(STR_ATTACHED);
        var $itemLast = $item.last();

        $(this).find("ul").toggleClass(STR_TOGGLE_BTN_GROUP);
        $item.addClass(STR_BTN_CONDITION);

        $item.each(function(index, el) {
          var $input = $(this).find("input");
          if ($input[0].checked) {
            $(this).addClass(STR_CHOOSE);
          }
          assistant.lineWrap($input.next("span"), $(this), NUM_LINE_WRAP_LENGTH_1);
        });

        if ($attached.length) {
          $itemLast.hide();
          if (!$itemLast.find("input")[0].checked) {
            $attached.find("input").val("");
          } else {
            $attached.addClass(STR_CHOOSE_OTHER);
          }
        }

        $(this).show();
      });
    },

    //默认复选项处理
    checkType: function() {
      $checkType.each(function(index, el) {
        var $item = $(this).find(STR_NOT_ATTACHED);
        $(this).find("ul").addClass(STR_CHECK_CHOOSE_GROUP).toggleClass(STR_TOGGLE_BTN_GROUP);

        $item.addClass(STR_BTN_CONDITION);

        $item.each(function(index, el) {
          var $input = $(this).find("input");

          if ($input[0].checked) {
            $(this).addClass(STR_CHOOSE);
          }

          assistant.lineWrap($input.next("span"), $(this), NUM_LINE_WRAP_LENGTH_1);
        });

        $(this).show();
      });
    },

    //是否是平行进口车选项特殊处理
    checkShifoupingxing: function() {
      var $ulCheckType = $ddCheckType_ShiFouPingXing.find("ul");
      var itemHTML = '<li class="yes ' + STR_BTN_CONDITION + '"><label>是</label></li>' +
        '<li class="not ' + STR_BTN_CONDITION + '"><label>否</label></li>';

      $formCheck_ShiFouPingXing.parents("li").hide();
      $ddCheckType_ShiFouPingXing.show();
      $ulCheckType.toggleClass(STR_TOGGLE_BTN_GROUP);
      $ulCheckType.append(itemHTML);

      $ulCheckType.find($formCheck_ShiFouPingXing[0].checked ? ".yes" : ".not").addClass(STR_CHOOSE);
    },

    //是否注册选项特殊处理
    radioShifouzhuce: function() {
      var value = $formRadio_ShiFouZhuCe.eq(0)[0].checked;
      var isChoose = value ? '' : ' ' + STR_CHOOSE;
      var itemHTML = '<ul class="' + STR_UNDER + " " + STR_CHOOSE_GROUP + '"><li class="' + STR_BTN_CONDITION + isChoose + '"><label>未注册</lable></li></ul>';

      $formRadio_ShiFouZhuCe.parent().hide();
      $formRadio_ShiFouZhuCe.parents("dd").append(itemHTML);
    },

    //过户详细记录
    textareaGuohuxiangxi: function() {
      var text = $formTextarea_GuoHuXiangXi.prev().text();
      var itemHTML = '<dl class="' + STR_DL_HORIZONTAL + " " + STR_CLEARFIX + '">' +
        '<dt><span>' + text + '</span></dt>' +
        '<dd class="' + STR_TEXTAREA_TYPE + '"></dd>' +
        '</dl>';
      var $item = $(itemHTML);
      $formTextarea_GuoHuXiangXi.prev().remove();
      $formTextarea_GuoHuXiangXi.parents("dl").after($item);
      $item.find("dd").append($formTextarea_GuoHuXiangXi);
      $textareaType = $formCheck.find("dd." + STR_TEXTAREA_TYPE); //重新定义$textareaType
    },

    //部分选项展开和收起
    spreadOrRetract: function() {
      $ulTagSpreadOrRetract.each(function() {
        var row = parseInt($(this).data(STR_DATA_NUM_ROWS)) || 0;
        var $item = $(this).find(STR_NOT_ATTACHED);
        var length = $item.length;
        var attachedLength = $(this).find(STR_ATTACHED).length;
        var $btn = $('<span class="' + STR_BTN_SPREAD_OR_RETRACT + '">' + STR_SPREAD_TEXT + '</span>');

        if (length - attachedLength <= NUM_CONDITION_ROW_VALUE * row) {
          return;
        }

        $item.eq(NUM_CONDITION_ROW_VALUE - 2).css("margin-right", $item.eq(0).outerWidth(true));
        $(this).after($btn);
        assistant.spreadOrRetractDefault($(this), $btn, row);
      })
    },

    //拒绝理由textarea添加间距
    textareaJujueliyou: function() {
      $formTextarea_JuJueLiYou.prev().remove();
      $formTextarea_JuJueLiYou.css("margin-top", 10);
    }
  };

  /*
   *事件
   *事件事件
   *事件事件事件
   *事件事件事件事件
   *事件事件事件事件事件
   *事件事件事件事件事件事件
   *事件事件事件事件事件事件事件
   **/

  //具体的事件
  var handle = {
    //文本框input
    textChange: function() {
      var value = $.trim($(this).val());
      if (value) {
        assistant.removeTip($(this));
        if ($(this).parent().hasClass('date')) {
          if ($(this).is($formText_ZhuCeRiQi) || $(this).is($formText_ChuChangRiQi)) {
            return;
          }
          if (!REG_DATE.test(value)) {
            assistant.addTip($(this));
          }
        }
      }
    },

    //文本域textarea
    textareaChange: function() {
      var value = $.trim($(this).val());
      if (value) {
        assistant.removeTip($(this));
      }
    },

    //单选
    chooseRadio: function($el) {
      var $parent = $el.parents("ul");
      var $attached = $el.nextAll(STR_ATTACHED);
      var $textarea = $el.parents("dd").find("textarea");
      var isLast = $el.is($parent.find("li:last"));
      var isFirst = $el.find("input").is($formRadio_GuoHuCiShu.first());

      //component跳过默认单选效果
      if(!$el.is(".component-li")){
        if ($el.find("input")[0].checked) {

          if (!isLast && $textarea.length) {
            $textarea.val("");
          }

          if (!$textarea.length || !(isLast && $textarea.length)) {
            isFirst && $formTextarea_GuoHuXiangXi.parents("dl").removeClass(STR_TIP);
            assistant.removeTip($el);
          }

          $el.addClass(STR_CHOOSE).siblings().removeClass(STR_CHOOSE);
          $attached.removeClass(STR_CHOOSE_OTHER);
          $attached.find("input").val("");

        }
      }
    },

    //单选(其它)
    chooseRadioAttached: function() {
      var value = $.trim($(this).val());
      if (value) {
        assistant.removeTip($(this));
        $(this).addClass(STR_CHOOSE_OTHER)
        $(this).prev().find("input")[0].checked = true;
        $(this).prevAll("." + STR_CHOOSE).removeClass(STR_CHOOSE);
      }
    },

    //单选有textarea,且选择的是最后一个选项(例：评估结果，选择拒绝放贷)
    chooseRadioTextarea: function() {
      var value = $.trim($(this).val());
      var isLastChecked = $(this).parent().find("li:last input")[0].checked;

      if (value && isLastChecked) {
        assistant.removeTip($(this));
      }
    },


    chooseRadioComponent: function() {
      var rate = 0;
      var $parent = $("#step5");
      var $component = $parent.find(".component");
      var $checked = $parent.find(".choose input[type='radio']:checked");
      var valueObj = {'head':0, 'side':0, 'tail':0, 'top':0, 'bottom':0, 'mechine':0} ;
      var componentRefuse = twigObject.componentRefuse;

      $checked.each(function() {
        var value = $(this).data("ratio");
        var group = $(this).data("group");
        var field = $(this).attr("name");
        //具体组件比率业务规则
        if(value == -1) {
          rate = -1;return false;
        } else {
          switch(group) {
            case '头部':
              if (componentRefuse == "1" ) {
                if (field == "form[field_7020][value]") {
                  if(value == 10) {
                    rate = -1;return false;
                  }
                } else if (field == "form[field_7030][value]") {
                  if(value == 10) {
                    rate = -1;return false;
                  }
                } else if (field == "form[field_7040][value]") {
                  if(value == 15) {
                    rate = -1;return false;
                  }
                }
              }
              valueObj.head = (valueObj.head>value)?valueObj.head:value;break;
            case '侧部':
              valueObj.side += value;break;
            case '尾部':
              if (componentRefuse == "1" ) {
                  if (field == "form[field_7240][value]") {
                    if(value == 10) {
                      rate = -1;return false;
                    }
                  } else if (field == "form[field_7250][value]") {
                    if(value == 15) {
                      rate = -1;return false;
                    }
                  }
                }
              valueObj.tail = (valueObj.tail>value)?valueObj.tail:value;break;
            case '底部':
              valueObj.bottom += value;break;
            case '机械装置':
              valueObj.mechine = (valueObj.mechine>value)?valueObj.mechine:value;break;
            default:break;
          }
        }
      })

      if(rate == -1) {
        //-1,选中事故车,填写折损率为-1
        $parent.find("input[value='事故车']").parents("li").addClass(STR_CHOOSE);
        $parent.find("input[value='事故车']").eq(0)[0].checked = true;
        $formRadio_PingGuJieGuo.eq(1)[0].checked = true;
        $formRadio_PingGuJieGuo.eq(1).parents("li").click();
        $("#rate").css({"background-color":"#E6E6FA"});
        $("#rate").val(rate);
      } else {
        for(var i in valueObj) {
          rate += valueObj[i];
        }
        //折损率大于等于30判定为事故车rate置为-1
        if(rate >= 30) {
          rate = -1;
          $parent.find("input[value='事故车']").parents("li").addClass(STR_CHOOSE);
          $parent.find("input[value='事故车']").eq(0)[0].checked = true;
          $formRadio_PingGuJieGuo.eq(1)[0].checked = true;
          $formRadio_PingGuJieGuo.eq(1).parents("li").click();
          $("#rate").css({"background-color":"#E6E6FA"});
          $("#rate").val(rate);
        } else {
          $("#rate").css({"background-color":"#E6E6FA"});
          $("#rate").val(rate+"%");
        }
      }
    },

    //tab切换特殊处理,评估折损率
    tabRadioComponent: function() {
      var rate = 0;
      var $parent = $("#step5");
      var $component = $parent.find(".component");
      var $checked = $parent.find(".choose input[type='radio']:checked");
      var valueObj = {'head':0, 'side':0, 'tail':0, 'top':0, 'bottom':0, 'mechine':0} ;
      var componentRefuse = twigObject.componentRefuse;

      $checked.each(function() {
        var value = $(this).data("ratio");
        var group = $(this).data("group");
        var field = $(this).attr("name");
        //具体组件比率业务规则
        if(value == -1) {
          rate = -1;return false;
        } else {
          switch(group) {
            case '头部':
              if (componentRefuse == "1" ) {
                if (field == "form[field_7020][value]") {
                  if(value == 10) {
                    rate = -1;return false;
                  }
                } else if (field == "form[field_7030][value]") {
                  if(value == 10) {
                    rate = -1;return false;
                  }
                } else if (field == "form[field_7040][value]") {
                  if(value == 15) {
                    rate = -1;return false;
                  }
                }
              }
              valueObj.head = (valueObj.head>value)?valueObj.head:value;break;
            case '侧部':
              valueObj.side += value;break;
            case '尾部':
              if (componentRefuse == "1" ) {
                  if (field == "form[field_7240][value]") {
                    if(value == 10) {
                      rate = -1;return false;
                    }
                  } else if (field == "form[field_7250][value]") {
                    if(value == 15) {
                      rate = -1;return false;
                    }
                  }
                }
              valueObj.tail = (valueObj.tail>value)?valueObj.tail:value;break;
            case '底部':
              valueObj.bottom += value;break;
            case '机械装置':
              valueObj.mechine = (valueObj.mechine>value)?valueObj.mechine:value;break;
            default:break;
          }
        }
      })
      
      if(rate == -1) {
        //-1,选中事故车,填写折损率为-1
        $("#rate").css({"background-color":"#E6E6FA"});
        $("#rate").val(rate);
      } else {
        for(var i in valueObj) {
          rate += valueObj[i];
        }
        //折损率大于等于30判定为事故车rate置为-1
        if(rate >= 30) {
          rate = -1;
          $("#rate").css({"background-color":"#E6E6FA"});
          $("#rate").val(rate);
        } else {
          $("#rate").css({"background-color":"#E6E6FA"});
        $("#rate").val(rate+"%");
      }
      }
    },

    //复选
    chooseCheck: function($el) {
      //特殊车况和评估结果相关联
      var selectedTeShuCheKuang = function() {
        var selected;
        if ($el.find("input").attr("name") == "form[field_4140][value][]") {
          $formCheck_TeShuCheKuang.each(function() {
            if ($(this)[0].checked) {
              selected = true;
            }
          })
          if (selected) {
            $formRadio_PingGuJieGuo.eq(1)[0].checked = true;
            $formRadio_PingGuJieGuo.eq(1).parents("li").click();
          }
        }
      }

      if ($el.find("input")[0].checked) {
        assistant.removeTip($el);
        $el.addClass(STR_CHOOSE);
      } else {
        $el.removeClass(STR_CHOOSE);
      }

      selectedTeShuCheKuang();

      //空调特殊处理
      if ($ddCheckTypeKongtiao.has($el[0]).length && $el.find("input")[0].checked) {
        switch ($el.index()) {
          case 0:
          case 1:
            $el.siblings(":first").removeClass(STR_CHOOSE);
            $el.siblings(":first").find("input")[0].checked = false;
            $el.siblings(":last").removeClass(STR_CHOOSE);
            $el.siblings(":last").find("input")[0].checked = false;
            break;
          case 2:
            $el.siblings(":last").removeClass(STR_CHOOSE);
            $el.siblings(":last").find("input")[0].checked = false;
            break;
          case 3:
            $el.siblings().removeClass(STR_CHOOSE);
            $el.siblings().each(function() {
              $(this).find("input")[0].checked = false;
            });
            break;
        }
      }
    },

    //更改是否是平行进口车
    chooseCheckShiFouPingXing: function() {
      $formCheck_ShiFouPingXing[0].checked = !($(this).index() - 1);
      $(this).addClass(STR_CHOOSE).siblings().removeClass(STR_CHOOSE);
    },

    //更改是否注册
    chooseRadioShiFouZhuCe: function(event) {
      var value, index, toggleSwitch;
      var origin = event.data.origin;

      if (origin == "text") {
        value = $.trim($(this).val());
        if (value && !REG_DATE.test(value)) {
          assistant.addTip($(this));
        }
        index = value ? 0 : 1;
        toggleSwitch = value ? false : true;
        $formRadio_ShiFouZhuCe.eq(index)[0].checked = true;
        $(this).parents("dd").find("." + STR_BTN_CONDITION).toggleClass(STR_CHOOSE, toggleSwitch);
      } else {
        $formText_ZhuCeRiQi.val("");
        $(this).addClass(STR_CHOOSE);
        $formRadio_ShiFouZhuCe.eq(1)[0].checked = true;
        assistant.removeTip($(this));
      }
    },

    //更改出厂日期
    changeChuChangRiQi: function(e) {
      var value = $(this).val();
      var thisYear = (new Date()).getFullYear();
      var year, num;

      if (value) {
        if (REG_DATE.test(value)) {
          year = (new Date(value)).getFullYear();
          num = Math.round((thisYear + 1 - year) / 15 * 100);
          num = num < 0 ? "" : num;
          $formText_ChengXinLv.val(num + "%");
        } else {
          $formText_ChengXinLv.val("%");
        }
      } else {
        $formText_ChengXinLv.val("%");
      }
      //触发车龄调整
      assistant.adjustYear();
    },

    //转换为大写实时监听(input propertychange)
    inputTypeUpper: function() {
      assistant.replaceValue($(this), /[^0-9a-zA-Z\-]/g, "");
      assistant.replaceValue($(this), /[a-z]/g, function(word) {
        return word.toLocaleUpperCase();
      });
    },

    //数字输入框实时监听(input propertychange)
    inputTypeNumber: function() {
      var isPercent = $(this).hasClass(STR_PRECENT);

      if (!isPercent) {
        assistant.replaceValue($(this), /[^\d]/g, "");
        assistant.replaceValue($(this), /^0(\d)/g, null, true);
      } else {
        assistant.replaceValue($(this), /^\./g, "");
        assistant.replaceValue($(this), /[^\d|\.|%]/g, "");
        assistant.replaceValue($(this), /%\S+/g, "%");
        assistant.replaceValue($(this), /\.\./g, ".");
        assistant.replaceValue($(this), /(\.\d+)\./g, null, true);
      }
    },

    //数字框更改(change)
    inputTypeNumberChange: function() {
      var isPercent = $(this).hasClass(STR_PRECENT);
      var value = $(this).val();
      var gouzhishuiValue;

      if (isPercent) {
        value = value.replace(/^0+(\d)/g, "$1");
        if (value == false) {
          value = "%";
        }
      }

      $(this).val(value);

      //手工调整指导价触发购置税和购入价更改
      if ($formText_TiaoZhengZhiDaoJia.is($(this))) {
        if (value) {
          gouzhishuiValue = Math.round(value / 11.7);
          $formText_GouZhiShui.val(gouzhishuiValue);
          $formText_GouRuJia.val(Math.round(value) + gouzhishuiValue);
        } else {
          $formText_GouZhiShui.val("");
          $formText_GouRuJia.val("");
        }
      }

      assistant.adjustYear();
    },

    //收起展开
    spreadOrRetract: function() {
      var $parent = $(this).parent();
      $parent.toggleClass(STR_SPREAD + " " + STR_RETRACT);
      $(this).text($parent.hasClass(STR_SPREAD) ? STR_SPREAD_TEXT : STR_RETRACT_TEXT);
    },

    //保存
    save: function(tipType, type, cb) {
      var saveStatus = assistant.saveStatus;
      var url = twigObject.pathDoCheck; //check.html.twig定义的全局变量
      if (twigObject.orderStatus == 3) {
        url = twigObject.pathDoConfirm;
      } //复检，替换保存路径
      var type = type || 2; //订单加解锁状态
      var stage;

      if (twigObject.origin != "admin" && assistant.isClickSubmit) {
        return;
      }

      //静默保存验证表单数据是否有改变
      //虽然此方法不对文本框等首末的空格作trim验证，但基本够用了
      if (tipType == 0 && saveStatus.serialize == $formCheck.serialize()) {
        cb && cb();
        return;
      }

      // 处理当前事故图片
      assistant.accidentImgHandle();

      stage = assistant.judgeStage();

      saveStatus.isSave = false;
      saveStatus.serialize = $formCheck.serialize();
      saveStatus.stage = stage > saveStatus.stage ? stage : saveStatus.stage;

      $.ajax({
        type: 'POST',
        url: url + "?stage=" + saveStatus.stage + "&type=" + type,
        data: saveStatus.serialize,
        success: function(ret) {
          saveStatus.isSave = true;
          assistant.saveTip(ret, tipType, type);
          if (ret.success) {
            cb && cb();
          } else {
            assistant.setIsClickSubmit(false);
          }
        },
        error: function() {
          saveStatus.isSave = true;
          assistant.setIsClickSubmit(false);
        }
      })
    },

    //准备提交
    readySubmit: function() {
      var validate = assistant.validate();
      var $validateFirstItem; //第一个错误的表单项
      var validateFirstTabIndex; //第一个有错误的tab的index
      var value = $.trim($formText_VIN.val());
      var maintainStatus = assistant.maintainStatus;
      var lsjStatus = assistant.lsjStatus;

      var cb = function() { //提交表单之后的回调
        $.ajax({
          url: twigObject.pathPrepareCheck,
          type: "POST",
          success: function(ret) {
            if (!ret.success) {
              assistant.setIsClickSubmit(false);
              commonMethod.alert({
                msgHTML: ret.message
              });
              return;
            }
            assistant.updateMaintainRecord(2, value, function() {
              if (!assistant.vinCheck(value)) {
								assistant.addTip($formText_VIN);
                commonMethod.prompt({
                  title: "确认提交",
                  msgHTML: STR_MESSAGE_VIN_SUBMIT,
                  confirm: function() {
                    if (twigObject.IfNeedRecheck == true) {
                      assistant.jumpLink(twigObject.pathPrimaryFinishCheck);
                    } else {
                      assistant.jumpLink(twigObject.pathFinishCheck);
                    }
                  }
                })
              } else {
                commonMethod.prompt({
                  title: "确认提交",
                  msgHTML: "确保信息已核对，是否提交？",
                  confirm: function() {
                    if (twigObject.IfNeedRecheck == true) {
                      assistant.jumpLink(twigObject.pathPrimaryFinishCheck);
                    } else {
                      assistant.jumpLink(twigObject.pathFinishCheck);
                    }
                  }
                })
              }
            });
          }
        })
      };

      if (validate) {
        // if(!twigObject.isXFTM && $formText_CheLingTiaoZheng.hasClass(STR_INPUT_RED)){//判断车龄调整是否为负数
        // 	assistant.autoCloseTip(STR_MESSAGE_CHELING);
        // 	return;
        // }
        if (!maintainStatus.hadResult || !lsjStatus.hadResult) { //当次操作判断维保查询结果以及老司机查询结果是否返回
          commonMethod.prompt({
            msgHTML: !maintainStatus.hadResult ? STR_MESSAGE_MAINTAINA : STR_MESSAGE_LSJ,
            confirm: function() {
              cb();
            }
          })
        } else {
          cb();
        }
      } else {

        assistant.setIsClickSubmit(false);

        validateFirstTabIndex = $tabBox.find("." + STR_TIP).eq(0).index();
        $validateFirstItem = $formCheck.find("." + STR_TAB_PANE).eq(validateFirstTabIndex).find("." + STR_TIP).eq(0);

        $tabItem.eq(validateFirstTabIndex).find("a").click();
        assistant.autoCloseTip(STR_MESSAGE_VALIDATE);

        setTimeout(function() { //定位出错项(H5方法)
          if ($validateFirstItem.prev().length) {
            $validateFirstItem.prev()[0].scrollIntoView();
          } else {
            $validateFirstItem.parent()[0].scrollIntoView();
          }
        }, 350)
      }
    },

    //更改vin
    changeVIN: function() {
      var value = $.trim($formText_VIN.val());
      var maintainStatus = assistant.maintainStatus;
      var oldValue = $formText_VIN.data("curval");
      if (!assistant.vinCheck(value)) {
        assistant.addTip($formText_VIN);
        assistant.autoCloseTip(STR_MESSAGE_VIN_MASSAGE);
      }
      if (oldValue == value) {
        return;
      }
      $formText_VIN.data("curval", value);
      maintainStatus.isQuery = false;
      maintainStatus.hadResult = true;
      clearInterval(maintainStatus.timer);
      maintainStatus.timer = null;
      $sameVinReportArea.hide();
      $checkHeaderFix.removeClass(STR_SHOW_POPUP);
      $sameVinReportContent.html("");
      assistant.sameVinReportList = null;

      $nowVIN.text(value);

      $tabItem.filter("[data-tabtitle='cwb']").removeClass(STR_TIP);

      if (value) {
        assistant.sameVinReport(value, true);
      }
    },

    //更改engine
    changeEngine: function() {
      $nowEngine.text($.trim($formText_Engine.val()));
    },

    //查维保
    searchMaintain: function($el) {
      var index = $el.index();
      var value = $.trim($formText_VIN.val());
      var engine = $.trim($formText_Engine.val());
      var maintainStatus = assistant.maintainStatus;
      var text = $el.text().substr(1);
      if (engine == "") {
        assistant.autoCloseTip(STR_MESSAGE_MAINTAINA_ERROR);
        return;
      }
      if (maintainStatus.isQuery) {
        assistant.autoCloseTip(STR_MESSAGE_MAINTAINA_YET);
      } else {
        if (!assistant.vinCheck(value)) {
					assistant.addTip($formText_VIN);
          commonMethod.prompt({
            msgHTML: STR_MESSAGE_VIN_SELECT,
            confirm: function() {
              assistant.sendMaintainRequest(index, value, engine);
            }
          })
        } else {
          commonMethod.prompt({
            msgHTML: STR_MESSAGE_MAINTAINA_CONFIRM + "<span class='c-red'>" + text + "</span>?",
            confirm: function() {
              assistant.sendMaintainRequest(index, value, engine);
            }
          })
        }
      }
    },

    //查老司机
    searchLSJ: function($el) {
      var licence = $.trim($formText_PaiZhaoHaoMa.val());
      var vin = $.trim($formText_VIN.val());
      var engineNumber = $.trim($formText_FaDongJiHao.val());
      var lsjStatus = assistant.lsjStatus;
      var text = "查询最新的记录会收费，";
      if (engineNumber == "" || licence == "") {
        assistant.autoCloseTip(STR_MESSAGE_MAINTAINA_ERROR);
        return;
      }
      if (lsjStatus.isQuery) {
        assistant.autoCloseTip(STR_MESSAGE_MAINTAINA_YET);
      } else {
        if (!assistant.vinCheck(vin)) {
					assistant.addTip($formText_VIN);
          commonMethod.prompt({
            msgHTML: text + STR_MESSAGE_VIN_SELECT,
            confirm: function() {
              assistant.sendLSJRequest(vin, engineNumber);
            }
          })
        } else {
          commonMethod.prompt({
            msgHTML: text + "确认查询吗？",
            confirm: function() {
              assistant.sendLSJRequest(vin, engineNumber, licence);
            }
          })
        }
      }
    },

    //切换tab
    tabItemSwitch: function($el) {
      var saveStatus = assistant.saveStatus;
      var tabtitle = $el.data("tabtitle");
      var vin = $.trim($formText_VIN.val());
      var engineNumber = $.trim($formText_FaDongJiHao.val());
      var productionDate = $.trim($formText_ChuChangRiQi.val());
      var displacement = $.trim($formText_PaiLiang.val());
      var power = $.trim($formText_GongLv.val());
      var datavin = $el.data("vin");

      switch (tabtitle) {
        case "cwb":
          $el.removeClass(STR_TIP);
          if (datavin != vin) {
            if (!assistant.vinCheck(vin)) {
              $searchMaintainRecord.bootstrapTable('destroy');
              $searchLSJRecord.bootstrapTable('destroy');
            } else {
              assistant.updateMaintainRecord(1, vin);
              assistant.updateLSJRecord(vin);
            }
          }
          $el.data("vin", vin);
          break;
        case "qdcx":
          if (saveStatus.isSave) {
            this.save(0, false);
          }
          datavin != vin && vehicleModelFilter.update(vin, productionDate, displacement, power);
          $el.data("vin", vin);
          break;
        case "clzj":
          if (saveStatus.isSave) {
            this.save(0, false);
          }
          break;
        case "clpz":
          if (saveStatus.isSave) {
            this.save(0, false);
          }
          break;
        case "clhj":
          assistant.appraisalYYC();
          this.tabRadioComponent();
          // 如果报告中没有数据的话，执行一次保存
          if (twigObject.hasData == false) {
            this.save(1,2);
          }
          break;
      }
      $checkFormBox.scrollTop(0);
    },
  };

  /*
   *审核主程序
   *审核主程序审核主程序
   *审核主程序审核主程序审核主程序
   *审核主程序审核主程序审核主程序审核主程序
   *审核主程序审核主程序审核主程序审核主程序审核主程序
   *审核主程序审核主程序审核主程序审核主程序审核主程序审核主程序
   *审核主程序审核主程序审核主程序审核主程序审核主程序审核主程序审核主程序
   **/

  //审核主程序
  var check = {
    init: function() {
      this.start(); //其它初始项
      this.reconsitution(); //重构dom
      this.binding(); //绑定事件
      this.vin(); //基于vin码的初始化
      this.interval(); //倒计时
      this.accidentImgInit(); //事故图片初始化
      this.initVideo(); //视频组件初始化
      this.initPrice();//几个价格的默认值0
    },

    start: function() {
      $(".input-group.date").datepicker({ //日期插件
        language: 'zh-CN',
        format: 'yyyy/mm/dd',
        orientation: "bottom left",
        autoclose: true,
        todayHighlight: true,
      });

      $(window).on("beforeunload", function(e) { //关闭页面提示
        return "确定离开？";
      });
    },

    //重构dom结构
    reconsitution: function() {
      reconsitutionDom.dtTitle();
      reconsitutionDom.textType();
      reconsitutionDom.radioType($radioType);
      reconsitutionDom.checkType();
      reconsitutionDom.checkShifoupingxing();
      reconsitutionDom.radioShifouzhuce();
      reconsitutionDom.textareaGuohuxiangxi();
      reconsitutionDom.spreadOrRetract();
      reconsitutionDom.textareaJujueliyou();
      if (twigObject.origin == "admin") {
        reconsitutionDom.chelingtiaozheng();
        reconsitutionDom.addSearchMaintain();

        //估价模块
        reconsitutionDom.radioType($appraisalRadioType);
      }
    },

    //绑定事件
    binding: function() {
      this.handleText();
      this.handleTextarea();
      this.handleRadio($radioType);
      this.handleCheck();
      this.handleSpecific();
      this.handleFormCheck();
      this.handleInputTypeNumber();
      this.handleInputTypeUpper();
      this.handleSearchMaintain();

      //估价模块
      this.handleRadio($appraisalRadioType);
      this.handleAppraisal();

      this.handleSameVinReport();

      this.handlePrice();
    },

    //基于vin的初始化
    vin: function() {
      var value = $.trim($formText_VIN.val());
      var productionDate = $.trim($formText_ChuChangRiQi.val());
      var displacement = $.trim($formText_PaiLiang.val());
      var power = $.trim($formText_GongLv.val());

      $nowVIN.text(value);
      $nowEngine.text($.trim($formText_Engine.val()));
      $formText_VIN.data("curval", value);

      $tabItem.filter("[data-tabtitle='cwb']").data("vin", "");
      $tabItem.filter("[data-tabtitle='qdcx']").data("vin", value);

      if (value) {
        assistant.sameVinReport(value); //相同vin历史报告初始化
      }

      //车型库初始化
      vehicleModelFilter = new VehicleModelFilter("#vehicleModelFilter", {
        vin: value,
        productionDate: productionDate,
        displacement: displacement,
        power: power,
        errorTip: function(msg) {
          assistant.autoCloseTip(msg);
        },
        manualInput: function() {
          //$tabItem.filter("[data-tabtitle='clpz']").find("a").click();
        },
        success: function(VMFilter, data) {
          $tabItem.filter("[data-tabtitle='clpz']").find("a").click();
          assistant.modelConfirm(data, VMFilter.common);
          //回填隐藏的品牌id，车系id，车型id，新车指导价
          $("#formCheck input[name='form[field_2011]']").val(data.data["brandId"][1]);
          $("#formCheck input[name='form[field_2021]']").val(data.data["seriesId"][1]);
          $("#formCheck input[name='form[field_2031]']").val(data.data.modelId);
          if (data.data['base_price'] != undefined) {
            $("#formCheck input[name='form[field_2081]']").val(data.data["base_price"][1]);
          } else {
            $("#formCheck input[name='form[field_2081]']").val('暂无数据');
          }
        }
      });
    },

    //倒计时
    interval: function() {
      var interval, minute;

      if (twigObject.limitMinutes == false) {
        $interval.hide();
        return;
      }
      interval = twigObject.interval;
      minute = (interval.d * 24 + interval.h) * 60 + interval.i;

      assistant.interval(interval, minute);

      if (!(interval.y || interval.m || interval.d)) {
        setInterval(function() {
          if (minute == 0) {
            interval.invert = 0;
          }
          interval.invert ? minute-- : minute++;
          assistant.interval(interval, minute);
        }, 60000)
      }
    },

    // 初始化事故图片checkbox显示
    accidentImgInit: function() {
      var value = hiddenAccidentImg.val();
      $(".module-default:contains('车况') li.finish, .module-default:contains('补充') li.finish").each(function(index, el) {
        var key = $(this).attr('data-qiniu-key');
        $(this).append("<div class='pic-checkbox'>" +
          "<input type='checkbox' class='cb' id=" + key + ((value !== '' && $.inArray(key, value.split(",")) != -1) ? " checked" : '') + ">" +

          "<label for=" + key + "></label>" + "</div>");
      });

      $('.pic-checkbox').click(function(event) {
        var count = $("input.cb:checked").length;
        if (count > 2) {
          commonMethod.alert({
            msgHTML: '事故图片最多勾选2张'
          });
          event.preventDefault();
        }
      });
    },

    //文本输入框事件
    handleText: function() {
      $textType.on("change.default blur.default", "input[type='text']", handle.textChange);
    },

    //文本域事件
    handleTextarea: function() {
      $textareaType.on("change.default", "textarea", handle.textareaChange);
    },

    //radio单选事件
    handleRadio: function($elements) {
      $elements.on("click.default", STR_NOT_ATTACHED, function(e) {
        if (e.target.tagName == "LABEL" || e.target.tagName == "SPAN") {
          return;
        }

        handle.chooseRadio($(this));
      });

      $elements.on("change.default", "li" + STR_ATTACHED, handle.chooseRadioAttached);

      $elements.on("change.default", "textarea", handle.chooseRadioTextarea);

      $elements.on("mousedown", "ul .component-li", assistant.componentCheckedRate);
    },

    //check复选事件
    handleCheck: function() {
      $checkType.on("click.default", "li", function(e) {
        if (e.target.tagName == "LABEL" || e.target.tagName == "SPAN") {
          return;
        }
        handle.chooseCheck($(this));
      })
    },

    //转换为大写(拍照号码、vin、发动机号)
    handleInputTypeUpper: function() {
      $upper.on("input.upper propertychange.upper", handle.inputTypeUpper);
      $formText_PaiZhaoHaoMa.on("change", function() {
        assistant.replaceValue($(this), /[a-z]/g, function(word) {
          return word.toLocaleUpperCase();
        });
      })
    },

    //number类型文本框
    handleInputTypeNumber: function() {
      $number.on("input.number propertychange.number", handle.inputTypeNumber);
      $number.on("blur.number", handle.inputTypeNumberChange);
    },

    //特殊处理的表单项事件
    handleSpecific: function() {
      //更改是否平行进口
      $ddCheckType_ShiFouPingXing.on("click.specific", "." + STR_BTN_CONDITION, handle.chooseCheckShiFouPingXing);

      //更改注册日期
      $formText_ZhuCeRiQi.on("change.specific blur.specific", {
        origin: "text"
      }, handle.chooseRadioShiFouZhuCe);

      //单击未注册按钮(注册日期)
      $formRadio_ShiFouZhuCe.eq(0).parents("dd").find("." + STR_BTN_CONDITION).on("click.specific", {
        origin: "radio"
      }, handle.chooseRadioShiFouZhuCe);

      //单选组(收起、展开)
      $ulTagSpreadOrRetract.next("." + STR_BTN_SPREAD_OR_RETRACT).on("click.specific", handle.spreadOrRetract);

      //更改出厂日期触发修改成新率(相应触发车龄调整)
      $formText_ChuChangRiQi.on("change.specific blur.specific", handle.changeChuChangRiQi);

      //更改VIN
      $formText_VIN.on("blur.specific", handle.changeVIN);
      $formText_Engine.on("blur.specific", handle.changeEngine);
    },

    //审核全局事件(保存，退回，准备提交，返回审核列表)
    handleFormCheck: function() {
      $btnSave.on("click", function() {
        handle.save(2, 2, function() {
          commonMethod.prompt({
            msgHTML: "保存成功<br />是否解锁并退出当前页面",
            confirmTxt: "是",
            cancelTxt: "否",
            confirm: function() {
              handle.save(2, 3, function() {
                $(window).off("beforeunload");
                window.location.href = window.history.go(-1);
              });
            }
          })
        });
      })

      $tabItem.on("click", function() {
        handle.tabItemSwitch($(this));
      });

      $tabPage.on("click", function() {
        var href = $(this).data("href");
        if (this.className == 'btn btn-danger first-next') {
          commonMethod.prompt({
            title: "确认基本信息",
            msgHTML: "请再确认一下基本信息是否正确，是否确认完毕？",
            confirm: function() {
              $tabItem.find("a[href='" + href + "']").click();
            }
          })
        } else {
          $tabItem.find("a[href='" + href + "']").click();
        }
      })

      $btnReadySubmit.on("click", function() {
        if (twigObject.origin != "admin" && assistant.isClickSubmit) {
          return;
        }
        handle.save(1, 2, function() {
          handle.readySubmit();
        });
        assistant.setIsClickSubmit(true)
      })

      $btnUntread.on("click", function() {
        commonMethod.prompt({
          title: "订单退回",
          msgHTML: "确定解锁并退回订单吗？",
          confirm: function() {
            handle.save(2, 3, function() {
              assistant.jumpLink(twigObject.pathBack);
            });
          }
        });
      })

      $returnTaskList.on("click", function() {
        var href = $(this).attr("href");
        commonMethod.prompt({
          title: "返回审核列表",
          msgHTML: "确定解锁并返回审核列表吗？",
          confirm: function() {
            handle.save(2, 3, function() {
              assistant.jumpLink(href);
            });
          }
        });
        return false;
      })
    },

    //查维保,查老司机模块相关
    handleSearchMaintain: function() {
      $formCheck.on("click", "." + STR_SEARCH_MAINTAIN_BTN, function() {
        handle.searchMaintain($(this));
      });
      $formCheck.on("click", ".search-lsj-btn", function() {
        handle.searchLSJ($(this));
      });
    },

    //估价模块相关
    handleAppraisal: function() {
      $appraisalSystem.on("click",function () {
      	if(_isChange){
					 assistant.appraisalSystem()
				}
      });
      // 又一车估价，车置宝车型库价格，北极星估价
    },

    //相同vin历史报告相关事件
    handleSameVinReport: function() {
      $sameVinReportIcon.on("click", function() {
        $checkHeaderFix.toggleClass(STR_SHOW_POPUP);
      });

      $("body").on("click.samevin", function(event) {
        if ($(event.target).hasClass(STR_NOTICE_AREA) || $(event.target).parents("." + STR_NOTICE_AREA).length) {
          return;
        } else {
          $checkHeaderFix.removeClass(STR_SHOW_POPUP);
        }
      });

      $sameVinReportContent.on("click", "." + STR_USE_THIS_APPRAISAL, function() {
        var index = $(this).parents("li").index();
        $checkHeaderFix.removeClass(STR_SHOW_POPUP);


        var report = assistant.sameVinReportList[index].report;
        var isAccident = report.field_4140 && report.field_4140.value ? true : false;
        if (isAccident === true) {
          commonMethod.alert({
            msgHTML: '该vin码的车是事故车，请记着勾选事故车图片！'
          });
        }

        dataBackfill.backfillAll(index);
      });
    },

    //视频插件
    initVideo: function() {
      if (twigObject.videoSrc) {
        var video = $('#video1').videoCt({
          title: ' ', //标题
          volume: 0, //音量
          playSpeed: true, //播放速度
          autoplay: true, //自动播放
          clarity: {
            src: [twigObject.videoSrc] //链接地址
          },
        });

        //扩展
        video.status; //状态
        video.currentTime; //当前时长
        video.duration; //总时长
        video.volume; //音量
        video.claritySrc; //链接地址
        video.playSpeed; //播放速度
        // video.cutover;                  //切换下个视频是否自动播放
      }
    },

    initPrice: function() {
        $formText_dsf.attr({
          placeholder: '第三方价可正可负',
        });
        // 只允许输入正负整数
        $(".positive-or-minus").keyup(function(event) {
            this.value=this.value.replace(/[^\-\d]/g,'');
            if(this.value.indexOf('-')>=0){
            this.value='-'+this.value.replace(/-/g,'');
            }
        });
    },

    //价格
    handlePrice: function() {
        $formText_ckj.css({"float":"left","width":"60%"});
        $formText_ckj.after('<input id="rate" type="text" class="form-control number blur allow-empty" style="float:right;width:40%;text-align:center;color:red" readonly>');
        $('.blur').blur(function(event) {
            var tbj_sgj = $formText_tbj_sgj.val() == '' ? 0 : parseInt($formText_tbj_sgj.val());
            var tbj_xsj = $formText_tbj_xsj.val() == '' ? 0 : parseInt($formText_tbj_xsj.val());
            var ckj = $formText_ckj.val() == '' ? 0 : parseInt($formText_ckj.val());
            var xpj = $formText_xpj.val() == '' ? 0 : parseInt($formText_xpj.val());
            var dsf = $formText_dsf.val() == '' ? 0 : parseInt($formText_dsf.val());

            $formText_ShouGouJia.val(tbj_sgj - ckj + xpj + dsf);
            $formText_XiaoShouJia.val(tbj_xsj - ckj + xpj + dsf);
        });
    }
  };

  check.init();
})
