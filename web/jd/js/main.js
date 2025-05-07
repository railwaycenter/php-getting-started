$(document).ready(function () {
    $("#item-list").DataTable({
        "language": {
            "info": "显示 _START_ - _END_ 总 _TOTAL_ 记录",
            "lengthMenu": "显示 _MENU_ 记录",
            "search": "搜索:",
            "paginate": {
                "first": "首页",
                "last": "尾页",
                "next": "下一页",
                "previous": "上一页"
            },
            "processing": "正在搜索"
        },
        "order": [[0, "desc"]],
        "paging": false,
        "info": false,
        "searching": false
        // "pageLength": 10,
        // "processing": true,
        // "serverSide": true,
        // "ajax": "getitems.php"
    });

    var originaldata = {};
    //var oldvalue = "";
    $("#item-list").on("click",".user_price,.item_name,.mall_name",function() {
        $('.user_price,.item_name,.mall_name').editable('save.php', {
            // id   : 'bookId',
            // name : 'user_price',
            indicator: 'Saving…',
            event: 'dblclick',
            //cssclass  : 'custom-css',
            submit: '保存',
            cancel: '取消',
            cancelcssclass: 'btn btn-danger',
            submitcssclass: 'btn btn-success',
            tooltip: '双击编辑',
            indicator: '正在保存',
            select: true,
            formid: 'formid',
            placeholder: '',
            onedit: function () {
                console.log('If I return false edition will be canceled');
                return true;
            },
            before: function () {
                console.log('Triggered before form appears');
                //console.log('oldvalue'+oldvalue);
            },
            onsubmit: function (settings, original) {
                console.log('Triggered before submit');
                console.log('refert' + original.revert);
                //console.log(settings);
                //简单验证
                var newValue = $(original).find('input').val();
                newValue = $.trim(newValue);
                if (newValue == original.revert)
                {
                    console.log("need not same");
                    $("#formid").append('<span class="text-red">请输入不同值</span>');
                    return false;
                }

                if (newValue == '')
                {
                    console.log('不能为空！');
                    return false;
                }
            },

            callback: function (result, settings, submitdata) {
                console.log('Triggered after submit');
                console.log('Result: ' + result);
                //console.log('Settings.width: ' + settings.width);
                console.log('Submitdata: ' + submitdata.field);
                console.log('Submitdata: ' + submitdata.oldvalue);
            },
            //onblur: "ignore",
            //submitdata : submitdata,
            //submitdata as a function example
            submitdata: function (oldValue, settings) {
                originaldata['oldvalue'] = oldValue;
                console.log("oldValue text: " + oldValue);
                //console.log(settings);
                //console.log("User submitted text: " + submitdata.value);
                return {
                    id: $(this).parent().attr('id'),
                    field: $(this).attr('field'),
                    oldvalue: oldValue
                }
            },

        });
    });
    /*
    $('.status').editable('save.php', {

        indicator: 'Saving…',
        event: 'dblclick',
        submit: '保存',
        cancel: '取消',
        cancelcssclass: 'btn btn-danger',
        submitcssclass: 'btn btn-success',
        tooltip: '双击编辑',
        indicator: '正在保存',
        select: true,
        formid: 'formid',
        onsubmit: function (settings, original) {
            console.log('refert' + original.revert);
            //简单验证
            var newValue = $(original).find('input').val();
            newValue = $.trim(newValue);
            if (newValue == original.revert)
            {
                console.log("need not same");
                $("#formid").append('<span class="text-red">请输入不同值</span>');
                return false;
            }
            if (newValue == '')
            {
                console.log('不能为空！');
                return false;
            }
        },
        submitdata: function (oldValue, settings) {
            originaldata['oldvalue'] = oldValue;
            return {
                id: $(this).parent().attr('id'),
                field: $(this).attr('field'),
                oldvalue: oldValue
            }
        },

    });
    */
    var xAxisData = []; // 初始化一个空数组
    var seriesData = [];
    var titleName = '';

    $("#item-list").on("click",".user_price",function(){
    //     console.log("on 点击一次");
    // });
    //
    //
    // $('.user_price').click(function () {
        var curBlock = $(this).parent();
        var priceCharts = $("#priceCharts");
        if ( priceCharts.parent().is( "th" ) ) {
            priceCharts.unwrap().unwrap();
        }
        // $("#priceCharts").unwrap();
        curBlock.after(priceCharts);
        priceCharts.wrap("<tr><th colspan='6'></th></tr>");
        //$("#main").parent().parent().removeClass("d-none");
        priceCharts.removeClass("d-none");

        $.post("save.php", {id: $(this).parent().attr('id'), field: "chartInfo"}, function (data, status) {
            xAxisData = [];
            seriesData = [];
            for (let o in data)
            {
                titleName = data[o].item_name;
                xAxisData.push(data[o].monitor_date);
                seriesData.push(data[o].monitor_price)
                console.log(seriesData);
            }

            // 基于准备好的dom，初始化echarts实例
            var myChart = echarts.init(document.getElementById('main'));

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: titleName
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    textStyle: {
                        fontSize: 16
                    },
                    top: '20px',
                    data: ['价格']
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: xAxisData
                },
                yAxis: {
                    axisLabel: {
                        formatter: '{value} 元'
                    },
                    boundaryGap: [0, '100%'],
                },
                series: [{
                    name: '价格',
                    type: 'line',
                    data: seriesData,
                    markPoint: {
                        data: [
                            {type: 'max', name: '最大值'},
                            {type: 'min', name: '最小值'}
                        ]
                    },
                    // markLine: {
                    //     data: [
                    //         {type: 'average', name: '平均值'}
                    //     ]
                    // }
                }]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
        }, "json");


    });

    $("#item-list").on("change",".custom-control-input",function() {
        // $('.custom-control-input').change(function () {
            var checkid = $(this).attr("checkid");
            var itemid = $(this).parent().parent().parent().attr("id");

            if (checkid == 1)
            {
                $(this).attr("checkid", "0");
                $.post("save.php", "field=status&value=0&id=" + itemid, function (data) {
                    //alert(data);
                }, "json");
            }
            else
            {
                $(this).attr("checkid", "1");
                $.post("save.php", "field=status&value=1&id=" + itemid, function (data) {
                    //alert(data);
                }, "json");
            }

        // })
    });

    $("#keywords").keydown(function(e){
        if(e.keyCode == 13){
            $('#searchsm').trigger("click");
        }
    });

    //搜索
    $("#search,#searchsm").click(function () {
        var keywords = $("#keywords").val();

        if (keywords.length <= 0)
        {
            keywords = "上海";
            $("#keywords").addClass("is-invalid");
            return false;
        }
        else
        {
            $("#keywords").removeClass("is-invalid");
        }

        //keywords = keywords.substring(0,26);

        $("#bg,.loading").show();

        $.post("./getsearchitems.php", "keywords=" + keywords, function (data) {
            if (data.error != '100')
            {
                $("#nav").hide();
                var priceCharts = $("#priceCharts").addClass('d-none');
                $("#item-list  tr:not(:first)").html("");
                $("body").append(priceCharts);
                // for (var o in data) {
                $.each(data, function (n, value) {
                    if (value.status === "1")
                    {
                        statusSwitch = "<div class=\"custom-control custom-switch custom-switch-off-danger custom-switch-on-success\"><input type=\"checkbox\" checked class=\"custom-control-input\" id=\"customSwitch"+value.id+"\" checkid=\"1\"><label class=\"custom-control-label\" for=\"customSwitch"+value.id+"\"></label></div>";
                    }
                    else
                    {
                        statusSwitch = "<div class=\"custom-control custom-switch custom-switch-off-danger custom-switch-on-success\"><input type=\"checkbox\" class=\"custom-control-input\" id=\"customSwitch"+value.id+"\"  checkid=\"0\"><label class=\"custom-control-label\" for=\"customSwitch"+value.id+"\"></label></div>";
                    }
                    $("#item-list").append("<tr id='"+ value.id+ "'> \n" +
                        "<th>"+value.id+"</th> \n" +
                        "<th class='item_name' field=\"item_name\"><a href='"+ value.item_url+"' target='_blank'>"+value.item_name+"</a></th> \n" +

                        "<th class='item_price' field=\"item_price\">"+value.item_price+"</th> \n" +
                        "<th class='user_price' field=\"user_price\">"+value.user_price+"</th> \n" +
                        "<th class='mall_name' field=\"mall_name\">"+value.mall_name+"</th> \n" +
                        "<th class='status' field=\"status\">"+statusSwitch+"</th> \n" +
                        "</tr>");
                });


            }
            else
            {
                $("#item-list  tr:not(:first)").html("");
                $("#item-list").append("<tr><td colspan=\"6\">无此记录</td></tr>");
            }
        }, "json");

    });
});