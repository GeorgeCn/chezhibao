(function(){
    var chart = {
        //图表初始化
        init:function(){
            var box = {
                width  : 800,
                height : 200
            };
            var startY = 5;
            
            var assessPrice;//评估价格

            if(chart_field_4010){
                assessPrice = chart_field_4010;
            }else{
                assessPrice = chart_field_4012;
            }

            var handleTwigPrice,
                price=[],
                trendDate=[],
                twigTrend = [],
                evalPrices,
                vertex,
                points;
            price = this.setPrice(price,chart_vehicleAge,assessPrice);
            vertex = this.setVerticalNumber(price);
            points = this.coordinatePoints(price,vertex,box,startY);

            this.setHorizontalNumber(trendDate,chart_examedat);
            this.createCanvas(points,box,startY);
            this.showPriceNumber(points,startY);
        },

        //根据接口价格数据重新计算价格趋势
        handleTwigPrice:function(twigTrend,evalPrices){
            var result = {
                price:[],
                trendDate:[]
            };
            var b2cPrice = evalPrices.b2c_price;
            var c2bPrice = evalPrices.c2b_price;
            if(twigTrend.length){
                for(var i=0,len=twigTrend.length;i<len;i++){
                    if(i==0){
                        result.price.push(b2cPrice);
                    }else{
                        result.price.push(b2cPrice*(twigTrend[i].eval_price/c2bPrice))
                    }
                    result.trendDate.push(twigTrend[i].trend_year);
                }
            }
            return result;
        },

        //设置innerText(为了兼容)
        setInnerText:function(element,text){
            if (typeof element.textContent == "string"){
                element.textContent = text;
            } else {
                element.innerText = text;
            }
        },

        //设置坐标值(价格)
        setPrice:function(price,chart_vehicleAge,assessPrice){
            var priceElement = document.getElementById("priceNumber").getElementsByTagName("span");
            var year;
            var assessPrice = parseFloat(assessPrice);
            var count = function(year,oldPrice){//忽略js浮点运算精度问题
                switch (year){
                    case 1:
                    case 2:
                    case 3:
                        return oldPrice*(1-0.15);
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                        return oldPrice*(1-0.1);
                    default:
                        return oldPrice*(1-0.05);
                }
            };

            if(!price.length){
                year = (chart_vehicleAge.m||chart_vehicleAge.d)?chart_vehicleAge.y+1:chart_vehicleAge.y;
                price.push(assessPrice);
                price.push(count(year+1,price[0]));
                price.push(count(year+2,price[1]));
            }

            for(var i=0,len=price.length;i<len;i++){
                price[i] = parseFloat(price[i].toFixed(2));
                price[i] = price[i]<0.01?0.01:price[i];
                this.setInnerText(priceElement[i],price[i]);
            }

            return price;
        },
        //设置图表横轴
        setHorizontalNumber:function(trendDate,chart_examedat){
            var horizontalElement = document.getElementById("horizontalAxis").getElementsByTagName("span");
            var year,month,item;
            var reg = /\d+/g;
            if(!trendDate.length){
                year = parseInt(reg.exec(chart_examedat)[0]);
                month = parseInt(reg.exec(chart_examedat)[0]);

                for(var i=0;i<3;i++){
                    item = (year+i)+"年"+month+"月";
                    trendDate.push(item);
                }
            }
            for(var i=0,len=trendDate.length;i<len;i++){
                this.setInnerText(horizontalElement[i],trendDate[i]);
            }
        },
        //设置图表纵轴
        setVerticalNumber:function(price){
            var verticalElement = document.getElementById("verticalAxis").getElementsByTagName("span");
            var max,mid,length,radix,vertical=[];

            max = Math.ceil(Math.max.apply(null,price));
            length = max.toString().split(".")[0].length;
            radix = length!=1?Math.pow(10,length-1)/2:1;

            if((max%radix)!=0){
                max = Math.ceil(max/radix)*radix;
            }

            vertical = [max,max/2,0];

            for(var i=0,len=verticalElement.length;i<len;i++){
                this.setInnerText(verticalElement[i],vertical[i]);
            }
            return max;
        },
        //计算单个坐标点
        coordinatePoints:function(price,vertex,box,startY){
            var pointsItem = null,
                points = [];

            for(var i=0,len=price.length;i<len;i++){
                pointsItem = {
                    x : Math.floor((box.width*333333/1000000)*(i+0.5)),
                    y : box.height-Math.floor(box.height*(price[i]*100/vertex*100)/10000)+startY
                }

                points.push(pointsItem);
            }

            return points;
        },
        //canvas画图
        createCanvas:function(points,box,startY){
            var canvasChart = document.getElementById("canvasChart");
            var context = canvasChart.getContext("2d");

            canvasChart.width = box.width;
            canvasChart.height = box.height+startY;

            this.drawChart(context,points,box,startY);
            this.drawPoints(context,points,box,startY);
        },
        //画不规则图表
        drawChart:function(context,points,box,startY){
            var gr = context.createLinearGradient(0,0,0,box.height);
            var y = (points[0].y-startY)<startY?startY:(points[0].y-startY);

            //添加颜色端点
            gr.addColorStop(0,'rgb(237,85,101)');
            gr.addColorStop(1,'rgb(251,221,224)');

            //应用fillStyle生成渐变
            context.fillStyle = gr;
            context.beginPath();
            context.moveTo(0,y);
            for(var i=0,len=points.length;i<len;i++){
                context.lineTo(points[i].x,points[i].y);
                if(i==len-1){
                    context.lineTo(box.width,points[i].y+startY);
                }
            }

            context.lineTo(box.width,box.height+startY);
            context.lineTo(0,box.height+startY);
            context.lineTo(0,y);
            context.closePath();
            context.fill();
        },
        //画坐标点
        drawPoints:function(context,points,box,startY){
            var radiusOut = startY;
            var radiusIn = 3;
            var fill = function(radius,fillStyle,point){
                context.beginPath();
                context.arc(point.x, point.y, radius, 0, 2*Math.PI, false);
                context.fillStyle = fillStyle;
                context.closePath();
                context.fill();
            };

            for(var i=0,len=points.length;i<len;i++){
                fill(radiusOut,"#ed5766",points[i]);
                fill(radiusIn,"white",points[i]);
            }
        },
        //显示价格数字
        showPriceNumber:function(points,startY){
            var priceNumber = document.getElementById("priceNumber");
            var value = priceNumber.getElementsByTagName("span");

            priceNumber.style.display = "block";

            for(var i=0,len=points.length;i<len;i++){
                var widths=document.documentElement.clientWidth || window.innerWidth
                if(widths>750){
                value[i].style.marginTop = (points[i].y-startY*5)+"px"                    
                }else if(widths<=750){
                value[i].style.marginTop =(points[i].y-startY*1)+"px"     
                }

            }
        }
    };
    var reportModuleFuturePrice = document.getElementById("reportModuleFuturePrice");
    if(reportModuleFuturePrice){
        window.onload = function(){
            chart.init();
            chart_canvas && chart_canvas();
        }
    }else{
        chart_canvas && chart_canvas();
    }
})();