/*!
 * vehicle-model-filter v0.2
 * Date: 2017-02-14
 */

(function(){
	var prePath = "http://vmodel.youyiche.com";
	//api地址
	var apiUrl = {
		getAllBrand: "/vm/allbrands", //获取品牌
		getSeriesByBrandid: "/vm/series", //通过品牌获取车系
		getSeriesByVin: "/vm/vin_series", //通过vin码获取车系
		getFilterResult: "/vm/model", //车型筛选
		getNiankuanBySeriesid: "/vm/niankuan", //通过车系获取年款
		getVehicleModelDetail: "/vm/model_detail" //车型详数据
	};

	var selectDefaultHTML = {
		seriesList: '<option value="">请选择车系</option>'
	};

	//各模块html字符串
	var moduleHtml = {

		//构造模块M1(筛选结果和筛选条件模块)
		M1 :    '<div class="vehicle-model-filter-module vehicle-model-filter-M1">' +
					'<div class="vehicle-model-filter-content">' +
						'<div class="vehicle-model-filter-result">' +
							'<dl>' +
								'<dt>' +
									'<div class="clearfix">' +
										'<span class="pull-left">共有<b class="result-number vehicle-stress"></b>款车符合条件：</span>' +
										'<a href="#" target="_blank" class="pull-right result-compare vehicle-stress">图片对比</a>' +
									'</div>' +
									
								'</dt>' +
								'<dd class="vehicle-m1-list">' +
									'<ul class="result-list"></ul>' +
								'</dd>' +
								'<dd class="vehicle-m1-more text-right">' +
									'<a href="#" class="vehicle-stress result-remainder" data-handle="handleM1LookMore"></a>' +
								'</dd>' +
								'<dd class="vehicle-m1-manual text-right">' +
									'<span class="vehicle-stress" data-handle="handleM1ToManual">上面这些车型都不对？请手工填写车型</span>' +
								'</dd>' +
							'</dl>' +
						'</div>' +
						'<div class="vehicle-model-filter-condition vehicle-model-filter-list">' +
							'<ul class="filter-list">' +
							'</ul>' +
						'</div>' +
						'<div class="vehicle-model-filter-footer vehicle-btn-group-specific">' + 
							'<span class="btn btn-danger btn-vehicle-sure" data-handle="handleM1ConfirmModelEnd">就是这款车</span>' + 
							'<p class="vehicle-jump"><span class="vehicle-stress" data-handle="handleM1ToManual">上面这款车不对？请手工填写车型</span></p>' +
						'</div>' + 
					'</div>' +
					'<div class="vehicle-model-filter-preloader">' + 
					'</div>' +
				'</div>',

		//构造模块M2(手动选择品牌等模块)
		M2 :    '<div class="vehicle-model-filter-module vehicle-model-filter-M2">' +
					'<div class="form-horizontal">' +
						'<div class="vehicle-model-filter-series">' +
							'<ul class="filter-series-list clearfix">' +
								'<li class="filter-series-item clearfix m2-select-brand">' +
									'<label class="filter-series-item-title control-label">品牌</label>' +
									'<div class="filter-series-item-content filter-series-item-brand">' +
										'<input type="text" class="form-control" data-handle-input="handleM2InputBrand" data-handle-change="handleM2ChangeBrand" placeholder="请选择品牌" />' +
										'<ul></ul>' +
									'</div>' +
								'</li>'	+
								'<li class="filter-series-item clearfix m2-select-series">' +
									'<label class="filter-series-item-title control-label">车系</label>' +
									'<div class="filter-series-item-content filter-series-item-series">' +
									'<select class="filter-series-select form-control" data-handle-change="handleM2ChangeSeries">' +
										selectDefaultHTML.seriesList +
									'</select>' +
									'</div>' +
								'</li>'	+	
							'</ul>' +
						'</div>' +
					'</div>' +
				'</div>'
	}

	//具体的业务处理
	function Work(){

	}

	Work.prototype = {

		constructor:Work,

		lineWrapLength:4,

		lineWrapLengthTitle:7,

		pinpaiList:null,

		//出错提示(可配置)
		errorTip : function(msg){
			alert(msg)
		},

		//确认车型后回调
		success:function(VMfilter,data){
			console.log(data);
		},

		//手动填写车型信息
		manualInput:function(){

		},

		//去除个位数月日前的0,并统一以"/"分割
		removeZero:function(dateStr){
			var dateArr1 = dateStr.split(/[\/-]/);
			var dateArr2 = [];
			$.each(dateArr1,function(){
				parseInt(this, 10) && dateArr2.push(parseInt(this, 10));
			})
			return dateArr2.join("/");
		},

		//ajax请求结果统一处理错误
		ajaxResponseError : function(data,cb){
			if(data.errno==0){
				cb(data);
			}else{
				this.errorTip(data.errmsg);
			}
		},

		//处理"选择条件"字符串折行
		lineWrapCondition : function(str,lineWrapLength){
			var result = {
				className:"",
				value:str
			};
			if(typeof str == "number"){
				str = str.toString();
			}
			if(str.length>lineWrapLength){
				if(str.length==lineWrapLength+1){
					result.className = " small";
				}else{
					result.className = " small linewrap";
					result.value = str.substring(0,lineWrapLength)+"<br />"+str.substring(lineWrapLength);
				}
			}

			return result;
		},

		//构造单个筛选(结果)的html字符串
		createHTMLSingleResult : function(item,index){
			var remainderItem = index>2?' remainder-item':'';
			var itemHTML =  '<li class="clearfix'+remainderItem+'">' +
								'<p class="title pull-left">' +
									'<a href="' + item[1] + '" target="_blank" title="'+item[0]+'">' + item[0] + '</a>' +
								'</p>' +
								'<p class="confirm pull-right">' +
									'<a href="#" class="vehicle-stress" data-modelid="'+item[2]+'" data-handle="handleM1ConfirmModel">是这款</a>' +
								'</p>' +
						    '</li>';

			return itemHTML;
		},

		//构造单个(未筛选条件)的html字符串(年选是复选)
		createHTMLSingleConditionNot : function(item,niankuan){
			var itemHTML,
				btnHTML = "",
				i,
				len = item[2].length,
				title,
				yet,
				condition,
				handle = niankuan?'handleM1ChooseNiankuan':'handleM1ChooseCondition';

			title = this.lineWrapCondition(item[1],this.lineWrapLengthTitle);

			for(i=0;i<len;i++){
				yet = "";
				condition = this.lineWrapCondition(item[2][i],this.lineWrapLength);

				if(niankuan && niankuan.length){
					for(var j=0;j<niankuan.length;j++){
						if(item[2][i]==niankuan[j]){
							yet = "yet ";
							break;
						}
					}
				}

				btnHTML += '<span class="'+yet+'btn-condition'+condition.className+'" data-handle="'+handle+'" title="'+item[2][i]+'">'+condition.value+'</span>';
			}
			
			itemHTML =  '<li class="filter-item clearfix">' +
							'<div class="filter-item-title'+title.className+'">'+title.value+'</div>' +
							'<div class="filter-item-content">' +
								'<div class="condition-list" data-name="'+item[1]+'" data-key="'+item[0]+'">' +
									btnHTML +
								'</div>' +
							'</div>' +
						'</li>';

			return itemHTML;
		},

		//构造单个(已筛选条件)的html字符串
		createHTMLSingleConditionHad : function(item){
			var title,
				condition,
				itemHTML;

			title = this.lineWrapCondition(item.name,this.lineWrapLengthTitle);
			condition = this.lineWrapCondition(item.value,this.lineWrapLength);

			itemHTML =  '<li class="filter-item clearfix">' +
								'<div class="filter-item-title'+title.className+'">'+title.value+'</div>' +
								'<div class="filter-item-content">' +
									'<div class="condition-list" data-name="'+item.name+'" data-key="'+item.key+'">' +
										'<span class="yet btn-condition'+condition.className+'" data-handle="handleM1CancelCondition" title="'+item.value+'">'+condition.value+'</span>' +
									'</div>' +
								'</div>' +
							'</li>';

			return itemHTML;
		},

		//构造年款选择项(复选)
		createHTMLNiankuanCondition : function(common){
			var niankuanHTML = "";
			var niankuanItem = ["niankuan","年款",this.niankuanList];

			niankuanHTML = this.createHTMLSingleConditionNot(niankuanItem,common.niankuan);

			return niankuanHTML;
		},

		//构造车系select的option
		createHTMLSelectList : function(list){
			var setionHTML = selectDefaultHTML.seriesList;

			for(var i=0,len=list.length;i<len;i++){
				setionHTML += '<option value="'+list[i][1]+'">'+list[i][0]+'</option>'
			}

			return setionHTML;
		},

		//获取品牌或者车系列表
		getBrandOrSeriesList : function(url,cb){
			var self = this;

			$.getJSON(url,function(data){
				self.ajaxResponseError(data,function(data){
					var list = data.data.list || [];
					cb(list);
				})
			})
		},

		//获取年款
		getNiankuanBySeriesid : function(common,cb){
			var self = this;
			var proyear = self.removeZero(common.productionDate);
			var url = apiUrl.getNiankuanBySeriesid+"?seriesid="+common.seriesid;
			if(proyear){
				url += "&proyear="+proyear;
			}
			$.getJSON(url,function(data){
				self.ajaxResponseError(data,function(){
					self.niankuanList = data.data.list.allyears;
					common.niankuan = self.updateNiankuan(data.data.list.seriesyear);
					self.updateAdditional(common,data.data.list.displacement,data.data.list.power);
					cb && cb();
				})
			})
		},

		//通过vin码获取车系
		getSeriesByVin : function(common,$elements,cb){
			var self = this,
				url;

			url = apiUrl.getSeriesByVin+"?vin="+common.vin;
			
			$.getJSON(url,function(data){
				if(data.errno!=0){
					self.invalidVIN($elements,common);
					//self.errorTip(data.errmsg);
				}else{
					common.seriesid = data.data.seriesid;
					common.brand = data.data.brand;
					common.series = data.data.series;
					if(!self.pinpaiList){
						self.getBrandList(common,$elements,function(){
							self.inputBrandAndSeries(common,common.brand,$elements,true);
						})
					}else{
						self.inputBrandAndSeries(common,common.brand,$elements,true);
					}
					self.getNiankuanBySeriesid(common,cb);
				}
			})
		},

		//获取筛选结果
		getFilterResult : function($elements,common,cb){
			var self = this;
			var staticParam = "?vin="+common.vin
							+ "&seriesid="+common.seriesid
							+ "&niankuan="+common.niankuan;
			var url = apiUrl.getFilterResult+staticParam;

			var param = {},
				key,
				value;

			for(var i=0,len=self.filterHad.length;i<len;i++){
				key = self.filterHad[i].key;
				value = self.filterHad[i].value;
				param[key] = value;
			}

			$elements.$content.hide();
			$elements.$loading.show();

			$.ajax({
				url:url,
				type:"POST",
				data:param,
				dataType:"json",
				success:function(data){
					self.ajaxResponseError(data,function(data){
						$elements.$loading.hide();
						$elements.$content.show();
						self.showFilterResult($elements,data,common);
						cb && cb(data);
					})
				}
			})
		},

		//获取车型详细数据
		getVehicleModelDetail:function(VMfilter,id){
			var self = this;
			var url = apiUrl.getVehicleModelDetail+"?modelid="+id;

			$.getJSON(url,function(data){
				self.ajaxResponseError(data,function(data){
					self.success(VMfilter,data);
				})
			})
		},

		//获取品牌列表
		getBrandList : function(common,$elements,cb){
			var self = this;
			self.getBrandOrSeriesList(apiUrl.getAllBrand,function(brandList){
				self.pinpaiList = brandList;
				cb && cb();
			})
		},

		//填充品牌和车系选择框
		inputBrandAndSeries:function(common,brand,$elements,isChooseSeriesid){
			var brandid=null;
			for(var i=0;i<this.pinpaiList.length;i++){
				if(brand==this.pinpaiList[i][0]){
					brandid = this.pinpaiList[i][1];
					break;
				}
			}
			if(brandid){
				$elements.$brandInput.val(brand);
				this.showSeriesList($elements,brandid,function(){
					isChooseSeriesid && $elements.$seriesList.val(common.seriesid);
				});
			}else{
				$elements.$seriesList.html(selectDefaultHTML.seriesList);
			}
		},

		//显示品牌列表
		showBrandList:function($elements,common,value){
			var result = [];
			var resultHTML = "";
			$.each(this.pinpaiList,function(){
				var name1 = this[0].toLocaleUpperCase();
				var name2 = this[2].toLocaleUpperCase();
				var val = value.toLocaleUpperCase();
				if(name1.indexOf(val)==0 || name2.indexOf(val)==0){
					result.push(this);
				}
			})
			$.each(result,function(){
				var itemHTML = '<li data-handle="handleM2SelectBrand" data-brandId="'+this[1]+'">'+this[0]+'</li>';
				resultHTML += itemHTML;
			})
			if(result.length){
				$elements.$brandList.html(resultHTML).show();
			}
		},

		//显示车系列表
		showSeriesList : function($elements,id,cb){
			var self = this;
			var url = apiUrl.getSeriesByBrandid + "?brandid=" + id;
			self.getBrandOrSeriesList(url,function(seriesList){
				var seriesListHTML = self.createHTMLSelectList(seriesList);
				$elements.$seriesList.html(seriesListHTML);
				cb && cb();
			})
		},

		//显示筛选结果
		showFilterResult : function($elements,data,common){
			var result = data.data.list,
				remainderText = "",
				filterNot = data.data.filter,
				filterHad = this.filterHad,
				rLength = result.length,
				fNotLength = filterNot.length,
				fHadLength = filterHad.length,
				resultHTML = "",
				filterHadHTML = "",
				filterNotHTML = "",
				i,
				j,
				k;

			$elements.$compare.attr("href",data.data.compare);
			$elements.$number.text(result.length);

			remainderText = result.length>3?("还有"+(result.length-3)+"条，点击展开查看"):"";
			$elements.$remainder.text(remainderText);

			for(i=0;i<rLength;i++){//符合条件的车型
				resultHTML += this.createHTMLSingleResult(result[i],i);
			}

			filterHadHTML += this.createHTMLNiankuanCondition(common);

			for(j=0;j<fHadLength;j++){//已选择的条件
				filterHadHTML += this.createHTMLSingleConditionHad(filterHad[j]);
			}

			for(k=0;k<fNotLength;k++){//未选择的条件
				filterNotHTML += this.createHTMLSingleConditionNot(filterNot[k]);
			}

			!$elements.$result.hasClass('had-show-remainder') && $elements.$result.toggleClass('show-remainder',rLength<4);

			$elements.$footer.toggleClass('show',rLength==1);

			$elements.$resultList.html(resultHTML);
			$elements.$filter.html(filterHadHTML+filterNotHTML);
		},

		//显示模块
		showModule : function($elements,index){
			$elements.$module.eq(index).show().siblings().hide();
		},

		//更新车系id并重新获取筛选结果
		updateSeriesid : function($elements,common,seriesid){
			var self = this;
			common.brand = $elements.$brandInput.val();
			common.series = $elements.$seriesList.find("option:selected").text();
			common.seriesid = seriesid;
			common.niankuan = [];
			this.filterHad = [];
			this.getNiankuanBySeriesid(common,function(){
				self.getFilterResult($elements,common);
			});
		},

	  	//更新年款
	  	updateNiankuan : function(seriesyear){
	  		var	niankuan = [],
				niankuanList = this.niankuanList;

			if(!seriesyear||seriesyear.length==0){
				return niankuan;
			}

			for(var i=0;i<seriesyear.length;i++){
				for(var j=0;j<niankuanList.length;j++){
					if(seriesyear[i]==niankuanList[j]){
						niankuan.push(seriesyear[i]);
					}
				}
			}

			return niankuan;
	  	},

	  	//更新附加参数(功率，排量)
	  	updateAdditional:function(common,displacement,power){
	  		var itemDisplacement = {
	  			key : displacement.attr,
				name : displacement.attrcn
	  		};
	  		var itemPower = {
	  			key : power.attr,
				name : power.attrcn
	  		};

	  		var pushItem = function(item,self,list,value){
	  			if(value){
	  				$.each(list,function(index, el) {
	  					//功率和排量都是number
	  					if(parseFloat(this)==parseFloat(value)){
		  					item.value = this;
		  					self.filterHad.push(item);
		  				}
		  			});
	  			}
	  		}
	  		// 暂时去掉排量，因为和车置宝的单位不一样
	  		//pushItem(itemDisplacement,this,displacement.value,common.displacement);
	  		pushItem(itemPower,this,power.value,common.power);
	  	},

	  	//无效vin处理
		invalidVIN:function($elements,common){
			$elements.$content.hide();
			if(!this.pinpaiList){
				this.getBrandList(common,$elements);
			}
			this.reset($elements);
		},

		//重置模块
		reset : function($elements){
			$elements.$compare.attr("href","#");
			$elements.$number.text("0");
			$elements.$resultList.html("");
			$elements.$filter.html("");
			$elements.$brandInput.val("");
			$elements.$seriesList.html(selectDefaultHTML.seriesList);
		},

	  	start:function($elements,common){
	  		var self = this;

	  		if(common.vin.length!=17){
	  			self.invalidVIN($elements,common);
	  			return;
	  		}

	  		self.getSeriesByVin(common,$elements,function(){
				self.getFilterResult($elements,common);
			})
	  	},

	  	init:function(VMfilter,$elements,common){
	  		var self = this;

	  		$(document).on("click.model",function(event){
	  			if(!$(event.target).parents(".filter-series-item-brand").length){
	  				$elements.$brandList && $elements.$brandList.hide();
	  			}
	  		})

	  		$elements.$wrap.on("click",function(event){
				var handle = $(event.target).data("handle");
				handle && event.preventDefault();
				handle && eventHandle[handle](VMfilter,$elements,self,common,event);
			});

			$elements.$wrap.on("change",function(event){
				var handle = $(event.target).data("handleChange");
				handle && eventHandle[handle](VMfilter,$elements,self,common,event);
			});

			$elements.$wrap.on("input propertychange",function(event){
				var handle = $(event.target).data("handleInput");
				handle && eventHandle[handle](VMfilter,$elements,self,common,event);
			});

			$elements.$brandInput.on("focus",function(event){
				var handle = $(event.target).data("handleInput");
				handle && eventHandle[handle](VMfilter,$elements,self,common,event);
			})

			self.start($elements,common);
	  	}
	}

	//事件handle
	var eventHandle = {

		//展开更多车型
		handleM1LookMore : function(VMfilter,$elements,work,common,event){
			$elements.$result.addClass('show-remainder had-show-remainder');
		},

		//进入手动选择模块
		handleM1ToManual : function(VMfilter,$elements,work,common,event){
			work.manualInput();
		},

		//确认车型
		handleM1ConfirmModel : function(VMfilter,$elements,work,common,event){
			var modelid = $(event.target).data("modelid");

			work.getVehicleModelDetail(VMfilter,modelid);
		},

		//确认车型(就是这款车，仅剩一款时)
		handleM1ConfirmModelEnd : function(VMfilter,$elements,work,common,event){
			var modelid = $elements.$resultList.find("li").first().find(".confirm a").data("modelid");
			work.getVehicleModelDetail(VMfilter,modelid);
		},

		//选择筛选条件(年款复选)
		handleM1ChooseNiankuan : function(VMfilter,$elements,work,common,event){
			var currentItem = $(event.target).attr("title");
			if($(event.target).hasClass('yet')){
				$.each(common.niankuan,function(index,item){
					if(item==currentItem){
						common.niankuan.splice(index,1);
					}
				})
			}else{
				common.niankuan.push(currentItem);
			}

			work.getFilterResult($elements,common);
		},

		//选择筛选条件
		handleM1ChooseCondition : function(VMfilter,$elements,work,common,event){
			var item = {
				key : $(event.target).parent().data("key"),
				name : $(event.target).parent().data("name"),
				value : $(event.target).attr("title")
			}

			work.filterHad.push(item);
			work.getFilterResult($elements,common);
		},

		//取消已选择的筛选条件
		handleM1CancelCondition : function(VMfilter,$elements,work,common,event){
			var index = $(event.target).parents("li").index()-1;

			work.filterHad.splice(index,1);
			work.getFilterResult($elements,common);
		},

		//输入品牌
		handleM2InputBrand : function(VMfilter,$elements,work,common,event){
			var value = $(event.target).val();
			event.stopPropagation();
			work.showBrandList($elements,common,value);
		},

		//选择品牌
		handleM2SelectBrand : function(VMfilter,$elements,work,common,event){
			var value = $(event.target).text();
			var brandid = $(event.target).data("brandid");
			$elements.$brandInput.val(value);
			$elements.$brandList.hide();
			work.showSeriesList($elements,brandid);
		},

		//更改品牌
		handleM2ChangeBrand : function(VMfilter,$elements,work,common,event){
			var value = $(event.target).val();
			work.inputBrandAndSeries(common,value,$elements);
		},

		//选择车系
		handleM2ChangeSeries : function(VMfilter,$elements,work,common,event){
			var seriesid = $(event.target).val();
			if(seriesid){
				work.updateSeriesid($elements,common,seriesid);
			}
		}
		
	}

	/**
	 *车型筛选类
	 *@param {String} selector jquery选择器
	 *@param {Object} options 车型筛选实例配置参数
	 */
	function VehicleModelFilter(selector,options){
		var work = new Work(),
			$elements,
			$wrap,
			$module,
			$M1,
			$M2,
			common=null,
			self = this,
			configure;

		$(selector).html("<div class=\"vehicle-model-filter-wrap\">"+moduleHtml.M2+moduleHtml.M1+"</div>");

		$wrap = $(selector).find(".vehicle-model-filter-wrap");
		$module = $wrap.find(".vehicle-model-filter-module");

		//历史原因，不得不反着来
		$M1 = $module.eq(1);
		$M2 = $module.eq(0);

		//业务需用到的全局$elements
		this.$elements = $elements = {
			$wrap : $wrap,
			$module : $module,
			$M1 : $M1,
			$M2 : $M2,
			$content : $M1.find(".vehicle-model-filter-content"),
			$loading : $M1.find(".vehicle-model-filter-preloader"),
			$compare : $M1.find(".result-compare"),
			$number : $M1.find(".result-number"),
			$remainder : $M1.find(".result-remainder"),
			$result : $M1.find(".vehicle-model-filter-result"),
			$resultList : $M1.find(".result-list"),
			$filter : $M1.find(".filter-list"),
			$footer : $M1.find(".vehicle-model-filter-footer"),
			$brandInput : $M2.find(".m2-select-brand input"),
			$brandList : $M2.find(".m2-select-brand ul"),
			$seriesList : $M2.find(".m2-select-series select")
		};

		this.work = work;

		configure = {//可以配置的
			vin:"",
			productionDate:"",
			displacement:"",
			power:"",
			errorTip:work.errorTip,
			success:work.success,
			manualInput:work.manualInput
		}

		$.extend(configure,options);

		//业务需用到的全局参数
		this.common = common = {
			vin:configure.vin, //vin码
			seriesid:"", //车系id
			productionDate:configure.productionDate,//生产日期
			displacement:configure.displacement,//排量
			power:configure.power,//功率
			niankuan:[], //年款(以','分割)
			brand:"",//当前品牌名称
			series:"",//当前车系名称
			model:"",//当前车型名称
			seriesyear:""//当前车型年款
		};

		work.errorTip = configure.errorTip;
		work.success = configure.success;
		work.manualInput = configure.manualInput;

		work.filterHad = [];//已确定的筛选条件
		work.niankuanList = [];//车系对应的年款列表

		work.init(this,$elements,common);

	}

	VehicleModelFilter.prototype = {

		constructor:VehicleModelFilter,

		update:function(vin,productionDate,displacement,power){
			this.common.vin = vin;
			this.common.productionDate = productionDate || "";
			this.common.displacement = displacement || "";
			this.common.power = power || "";
			this.common.niankuan = [];
			this.common.brand = "";
			this.common.series = "";
			this.common.model = "";
			this.common.seriesyear = "";
			this.work.filterHad = [];
			this.work.niankuanList = [];
			this.work.start(this.$elements,this.common);
		}
	}

	window.VehicleModelFilter = VehicleModelFilter;
})($)