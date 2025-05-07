<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <!-- 设置页面字符编码为 UTF-8 -->
    <meta charset="utf-8">
    <!-- 兼容 IE 浏览器 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- 页面标题 -->
    <title>虎牙直连</title>
    <!-- 设置 viewport 以支持响应式设计，适配手机端 -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 引入 Font Awesome 图标库 -->
    <link rel="stylesheet" href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-10-y/font-awesome/6.0.0/css/all.min.css">
    <!-- 引入 Bootstrap CSS -->
    <link rel="stylesheet" href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-10-y/bootstrap/4.6.1/css/bootstrap.min.css">
    <!-- 引入 DataTables Bootstrap 样式 -->
    <link rel="stylesheet" href="https://lf9-cdn-tos.bytecdntp.com/cdn/expire-10-y/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
    <!-- 引入 AdminLTE 样式 -->
    <link rel="stylesheet" href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-10-y/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- 引入 autoComplete.js 样式 -->
    <link rel="stylesheet" href="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-10-y/tarekraafat-autocomplete.js/10.2.6/css/autoComplete.min.css">

    <style>
        /* 提示框样式 */
        #message-box {
            display: none; /* 默认隐藏提示框 */
            position: fixed; /* 固定位置显示 */
            top: 20px; /* 距离顶部 20px */
            left: 50%; /* 水平居中 */
            transform: translateX(-50%); /* 偏移调整居中 */
            padding: 15px; /* 内边距 */
            background-color: #4CAF50; /* 绿色背景 */
            color: white; /* 白色文字 */
            border-radius: 5px; /* 圆角 */
            font-size: 16px; /* 字体大小 */
            z-index: 1050; /* 调整 z-index 高于Bootstrap 模态框 (1040) */
            max-width: 90%; /* 限制宽度，避免溢出 */
            word-wrap: break-word; /* 文字自动换行 */
        }

        /* 选项卡样式调整 */
        .nav-tabs {
            background-color: #f8f9fa; /* 浅灰背景，增加对比 */
            border-bottom: 2px solid #007bff; /* 蓝色底部边框 */
        }
        .nav-tabs .nav-link {
            padding: 10px 20px; /* 增加内边距，突出显示 */
            font-weight: bold; /* 加粗字体 */
            color: #495057; /* 深灰文字 */
            border: none; /* 移除默认边框 */
        }
        .nav-tabs .nav-link:hover {
            background-color: #e9ecef; /* 悬停时背景变色 */
        }
        .nav-tabs .nav-link.active {
            background-color: #007bff; /* 蓝色背景，与图片标题栏一致 */
            color: white; /* 白色文字 */
            border-radius: 4px 4px 0 0; /* 上方圆角 */
        }
        .tab-content {
            padding: 15px; /* 内容区域内边距 */
            background-color: #fff; /* 白色背景 */
            border: 1px solid #dee2e6; /* 卡片边框 */
            border-top: none; /* 移除顶部边框，与选项卡衔接 */
        }

        /* 按钮组样式 */
        .btn-group .btn {
            margin-right: 5px; /* 按钮间距 */
            font-size: 0.9rem; /* 稍小字体，紧凑显示 */
        }
        .btn-group .btn.active {
            background-color: #17a2b8; /* 选中时的青色背景 */
            border-color: #17a2b8; /* 边框颜色 */
            color: white; /* 白色文字 */
        }
        .btn-outline-warning.active {
            background-color: #ffc107; /* FLV/HLS 选中时的黄色背景 */
            border-color: #ffc107;
            color: #212529; /* 深色文字 */
        }

        /* 保存按钮组样式调整 */
        #save-btn-group .btn {
            margin-right: 5px; /* PC 端按钮间距 */
            white-space: nowrap; /* 防止按钮文本换行 */
        }

        /* 最近播放按钮样式 */
        .recent-play-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* 按钮之间的间距 */
        }

        .recent-play-buttons .btn {
            font-size: 0.9rem;
            padding: 0.375rem 0.75rem;
            white-space: nowrap;
        }

        /* 自动补全列表样式 */
        .autoComplete_wrapper {
            position: relative; /* 确保定位上下文 */
        }
        .autoComplete_wrapper > ul {
            z-index: 2000; /* 高于其他元素，避免被覆盖 */
            max-height: 200px; /* 限制高度，防止溢出 */
            overflow-y: auto; /* 允许滚动 */
            position: absolute; /* 绝对定位 */
            width: 100%; /* 与输入框同宽 */
        }

        /* 美化链接按钮 */
        .btn-link-custom {
            font-size: 0.9rem; /* 与其他按钮字体一致 */
            padding: 0.375rem 0.75rem; /* 与按钮内边距一致 */
        }

        /* 手机端调整 */
        @media (max-width: 768px) {
            .btn-group .btn, .btn-link-custom {
                margin-bottom: 5px; /* 按钮垂直排列 */
                margin-right: 0;
                width: 100%; /* 占满宽度 */
            }
            #save-btn-group {
                display: flex; /* 使用 Flexbox 布局 */
                justify-content: space-between; /* 按钮之间均匀分布 */
                width: 100%; /* 占满宽度 */
            }
            #save-btn-group .btn {
                margin-right: 5px; /* 小屏时保持与 PC 端一致的左右间距 */
                margin-bottom: 10px; /* 小屏时保存按钮之间的垂直间隔 */
                flex: 1; /* 平均分配宽度 */
            }
            #save-btn-group .btn:last-child {
                margin-right: 0; /* 最后一个按钮无右间距 */
            }
            .form-row > div {
                margin-bottom: 10px; /* 输入框间距 */
            }
            /* 小屏时 CDN 和格式选项一行显示 */
            #cdn-group {
                display: flex; /* 使用 Flexbox 布局 */
                flex-wrap: nowrap; /* 不换行 */
                width: auto; /* 自适应宽度 */
                overflow-x: auto; /* 如果内容超出，允许水平滚动 */
            }
            #cdn-group .btn {
                font-size: 1rem; /* 保持原始字体大小 */
                padding: 0.375rem 0.5rem; /* 稍微减小内边距以适应小屏 */
                white-space: nowrap; /* 强制文字不换行 */
                flex: 1; /* 平均分配空间 */
                min-width: 0; /* 防止按钮宽度溢出 */
            }
            #media-group {
                display: inline-flex; /* 使用 Flexbox 让按钮组在一行 */
                flex-wrap: nowrap; /* 不换行 */
                width: auto; /* 自适应宽度 */
            }
            #media-group .btn {
                font-size: 1rem; /* 保持原始字体大小 */
                padding: 0.375rem 0.5rem; /* 保持格式选项的内边距 */
                white-space: nowrap; /* 强制文字不换行 */
            }
            .form-row.align-items-center {
                flex-wrap: wrap; /* 允许换行 */
            }
            .layout-cdn,.layout-media {
                flex: 1; /* CDN 和格式选项弹性分配空间 */
                max-width: none; /* 移除最大宽度限制 */
            }
            .layout-parse,.layout-links,.layout-parse2 {
                flex: 0 0 50%; /* 解析按钮和链接占满一行 */
                max-width: 100%;
            }
            .layout-links {
                display: flex; /* 链接按钮在一行 */
                justify-content: space-between; /* 链接按钮均匀分布 */
            }
            /* 输入 ID 占满一行 */
            .form-row.mb-3 .col-md-6 {
                flex: 0 0 100%; /* 输入 ID 占满一行 */
                max-width: 100%;
                padding-left: 0; /* 移除左侧内边距 */
                padding-right: 0; /* 移除右侧内边距 */
            }

            #bid {
                width: 100%; /* 强制输入框宽度占满容器 */
            }
            /* 最近播放按钮小屏调整 */
            .recent-play-buttons {
                flex-direction: column; /* 小屏时垂直排列 */
                gap: 8px; /* 减小间距 */
            }
            .recent-play-buttons .btn {
                width: 100%; /* 小屏时按钮占满宽度 */
            }
        }

        /* 769px 到 991px 调整 */
        @media (min-width: 769px) and (max-width: 991px) {
            /* 调整容器布局 */
            .layout-container {
                display: flex;
                flex-wrap: wrap; /* 允许换行 */
            }

            /* 解析按钮和链接占满一行 */
            .layout-parse,
            .layout-links,
            .layout-parse2 {
                flex: 0 0 50%; /* 占满一行 */
                max-width: 100%;
            }

            /* 链接部分调整 */
            .layout-links {
                display: flex; /* 链接按钮在一行 */
                justify-content: space-between; /* 均匀分布 */
                margin-top: 10px; /* 与上一行增加间距 */
            }

            /* CDN 和媒体类型弹性分配空间 */
            .layout-cdn,
            .layout-media {
                flex: 1; /* 弹性分配空间 */
                max-width: none; /* 移除最大宽度限制 */
                margin-top:10px;
            }

            /* 调整 CDN 和媒体类型按钮组 */
            #cdn-group,
            #media-group {
                display: inline-flex; /* 按钮组在一行 */
                flex-wrap: nowrap; /* 不换行 */
                width: auto; /* 自适应宽度 */
            }

            #cdn-group .btn,
            #media-group .btn {
                font-size: 1rem; /* 保持字体大小 */
                padding: 0.375rem 0.5rem; /* 统一内边距 */
                white-space: nowrap; /* 强制文字不换行 */
            }

            /* 解析按钮调整 */
            .layout-parse #btnParse {
                margin-top: 10px; /* 与上一行增加间距 */
            }

            /* 新增解析按钮调整 */
            .layout-parse2 #btnParse2 {
                margin-top: 10px; /* 与上一行增加间距 */
            }

            /* 链接按钮调整 */
            .layout-links .btn-link-custom {
                flex: 1; /* 平均分配宽度 */
                margin-right: 5px; /* 链接按钮间距 */
            }

            .layout-links .btn-link-custom:last-child {
                margin-right: 0; /* 最后一个链接按钮无右间距 */
            }
        }
    </style>
</head>
<body>
<!-- 主容器 -->
<div class="container my-4">
    <!-- 播放器区域 -->
    <div class="row mb-3">
        <div class="col-12">
            <!-- 播放器容器 -->
            <div id="dplayer" class="shadow-sm"></div>
        </div>
    </div>

    <!-- 主输入区域 -->
    <div class="row mb-3">
        <div class="col-12">
            <!-- 主输入区域：ID、CDN、媒体类型和提交按钮 -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">虎牙房间信息</h5> <!-- 与图片标题一致 -->
                </div>
                <div class="card-body">
                    <!-- 虎牙 ID 输入 -->
                    <div class="form-row mb-3">
                        <div class="col-md-6">
                            <div class="autoComplete_wrapper">
                                <input type="search" name="bid" id="bid" class="form-control" placeholder="请输入虎牙ID" list="appNamelist">
                            </div>
                        </div>
                    </div>
                    <!-- CDN、媒体类型、提交按钮和链接 -->
                    <div class="form-row align-items-center layout-container">
                        <!-- 解析按钮 -->
                        <div class="col-md-1 layout-parse">
                            <button class="btn btn-success btn-block" id="btnParse" type="button">解析</button>
                        </div>
                        <!-- 新增解析按钮 -->
                        <div class="col-md-1 layout-parse2">
                            <button class="btn btn-success btn-block" id="btnParse2" type="button">解析2</button>
                        </div>
                        <!-- 外部链接 -->
                        <div class="col-md-2 layout-links">
                            <a href="https://www.huya.com/g/wzry#cate-0-0" target="_blank" class="btn btn-outline-primary btn-link-custom">虎牙直播地址</a>
                        </div>
                        <div class="col-md-2 layout-links">
                            <a href="/pg.php" target="_blank" class="btn btn-outline-primary btn-link-custom">直播管理地址</a>
                        </div>
                        <!-- CDN 选择 -->
                        <div class="col-md-4 layout-cdn">
                            <div class="btn-group" id="cdn-group" role="group">
                                <button type="button" class="btn btn-outline-info active" data-cdn="hscdn">华为CDN</button>
                                <button type="button" class="btn btn-outline-info" data-cdn="txcdn">腾讯CDN</button>
                                <button type="button" class="btn btn-outline-info" data-cdn="hycdn">虎牙CDN</button>
                                <button type="button" class="btn btn-outline-info" data-cdn="alicdn">阿里CDN</button>
                            </div>
                        </div>
                        <!-- 媒体类型选择 -->
                        <div class="col-md-2 layout-media">
                            <div class="btn-group" id="media-group" role="group">
                                <button type="button" class="btn btn-outline-warning active" data-media="flv">FLV</button>
                                <button type="button" class="btn btn-outline-warning" data-media="hls">HLS</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 最近播放区域 -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">最近播放</h5>
                </div>
                <div class="card-body">
                    <div class="recent-play-buttons" id="recentPlayButtons">
                        <!-- 动态生成最近播放按钮 -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 本地和网络保存区域 -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">保存房间信息</h5> <!-- 与图片标题一致 -->
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <!-- 虎牙 ID 输入框 -->
                        <div class="col-md-5 mb-2">
                            <input type="text" name="saveRoomId" id="saveRoomId" class="form-control" placeholder="请输入虎牙ID">
                        </div>
                        <!-- 房间名输入框 -->
                        <div class="col-md-5 mb-2">
                            <input type="text" name="saveRoomName" id="saveRoomName" class="form-control" placeholder="请输入虎牙房间名">
                        </div>
                        <!-- 保存按钮组 -->
                        <div class="col-md-2 mb-2">
                            <div class="btn-group" id="save-btn-group" role="group">
                                <button class="btn btn-success" id="btnSaveLocal" data-action="local" type="button">本地保存</button>
                                <button class="btn btn-success" id="btnSaveNetwork" data-action="network" type="button">网络保存</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 消息提示框 -->
    <div id="message-box"></div>
</div>

<!-- 引入外部 JS 文件 -->
<script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-10-y/jquery/3.5.1/jquery.min.js"></script>
<script src="https://lf6-cdn-tos.bytecdntp.com/cdn/expire-10-y/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-10-y/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="https://lf6-cdn-tos.bytecdntp.com/cdn/expire-10-y/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://lf6-cdn-tos.bytecdntp.com/cdn/expire-10-y/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script src="https://lf9-cdn-tos.bytecdntp.com/cdn/expire-10-y/tarekraafat-autocomplete.js/10.2.6/autoComplete.min.js"></script>
<script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-10-y/hls.js/1.1.5/hls.min.js"></script>
<script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-10-y/flv.js/1.6.2/flv.min.js"></script>
<script src="https://lf3-cdn-tos.bytecdntp.com/cdn/expire-10-y/dplayer/1.26.0/DPlayer.min.js"></script>

<script>
    // 初始化 DPlayer 播放器
    let dp = new DPlayer({
        container: document.getElementById('dplayer'), // 指定播放器容器
        live: true, // 启用直播模式
        autoplay: true, // 启用自动播放
        video: {
            url: '', // 初始视频地址为空
            type: 'auto', // 自动检测视频类型
        },
    });

    // 定义初始房间数据，用于自动补全下拉列表
    let newdata = [
        "859042<br>正恒-紫宸【相声木兰】",
        "330679<br>怀逝【李白导师】",
        "391946<br>小炎【妲己的神】",
        "691346<br>宇晨【马可导师】",
        "825912<br>念青【嘴强王者】"
    ];

    // 配置自动补全功能
    const autoCompleteJS = new autoComplete({
        selector: "#bid", // 绑定到 #bid 输入框
        placeHolder: "", // 无默认占位符
        threshold: 0, // 输入任意字符即显示建议
        data: {
            src: newdata, // 数据源
            cache: false, // 不缓存数据，每次重新加载
        },
        resultsList: {
            element: (list, data) => { // 自定义结果列表
                if (!data.results.length) { // 如果没有匹配结果
                    const message = document.createElement("div");
                    message.setAttribute("class", "no_result");
                    message.innerHTML = `<span>Found No Results for "${data.query}"</span>`;
                    list.prepend(message); // 在列表顶部显示无结果提示
                }
            },
            noResults: true, // 启用无结果提示
            maxResults: undefined, // 不限制最大结果数
        },
        resultItem: {
            highlight: true // 高亮匹配部分
        },
        events: {
            input: {
                selection: (event) => { // 用户选择建议项时
                    const selection = event.detail.selection.value;
                    autoCompleteJS.input.value = selection.split('<br>')[0]; // 只取 ID 部分填入输入框
                },
                focus: (event) => { // 输入框聚焦时
                    autoCompleteJS.start(); // 开始自动补全
                }
            }
        }
    });

    // 显示消息提示的函数
    function showMessage(message, duration = 3000) {
        const messageBox = $("#message-box"); // 获取提示框元素
        messageBox.text(message).fadeIn(); // 设置消息并淡入显示
        setTimeout(function () {
            messageBox.fadeOut(); // 指定时间后淡出隐藏
        }, duration); // 默认显示 3 秒
    }

    // 页面加载完成后执行的初始化逻辑
    $(document).ready(function () {
        // 发送 AJAX 请求的通用函数
        function sendRequest(action, data, callback) {
            const xhr = new XMLHttpRequest(); // 创建 XMLHttpRequest 对象
            xhr.open('POST', 'pgsql.php', true); // 设置 POST 请求到 pgsql.php
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); // 设置请求头
            xhr.onreadystatechange = function () { // 请求状态变化时触发
                if (xhr.readyState === XMLHttpRequest.DONE) { // 请求完成
                    if (xhr.status === 200) { // 请求成功
                        try {
                            const jsonResponse = JSON.parse(xhr.responseText); // 解析 JSON 响应
                            callback(jsonResponse); // 执行回调函数
                        } catch (e) {
                            console.error("Failed to parse JSON response: ", e); // 解析失败时记录错误
                        }
                    } else {
                        console.error("Request failed: ", xhr.status); // 请求失败时记录状态码
                    }
                }
            };
            xhr.send(`action=${encodeURIComponent(action)}&${data}`); // 发送请求数据
        }

        // 更新播放流的函数
        function updateStream(id) {
            const cdn = $("#cdn-group .btn.active").data('cdn'); // 获取选中的 CDN
            const media = $("#media-group .btn.active").data('media'); // 获取选中的媒体类型
            // 禁用解析按钮
            $("#btnParse").prop('disabled', true);
            $.ajax({
                url: 'huyaapi.php', // 修改为 huyaapi.php
                method: 'GET', // 使用 GET 方法
                data: { id: id, cdn: cdn, media: media }, // 传递 ID、CDN 和媒体类型参数
                dataType: 'json', // 预期返回 JSON 数据
                success: function (response) { // 请求成功回调
                    console.log("Realdata:", response.realdata); // 记录调试数据
                    console.log("format_result:", response.format_result);
                    console.log("mediaurl:", response.url);
                    if (response.url) { // 如果返回有效 URL
                        dp.switchVideo({ url: response.url, type: 'auto' }); // 切换播放器视频
                        dp.play(); // 自动播放
                        showMessage("播放地址已更新并开始播放！"); // 显示成功提示
                        // 保存到最近播放
                        saveRecentPlay(id);
                    } else if (response.error) { // 如果返回错误信息
                        let errorMsg = `错误: ${response.error}`;
                        if (response.realdata) { // 如果包含调试数据
                            errorMsg += `\n调试信息: ${JSON.stringify(response.realdata, null, 2)}`;
                        }
                        if (response.unknown_cdn_types) { // 如果包含未知 CDN 类型
                            errorMsg += `\n未知 CDN 类型: ${JSON.stringify(response.unknown_cdn_types)}`;
                        }
                        showMessage(errorMsg); // 显示错误消息
                        console.log("Realdata:", response.realdata); // 记录调试数据
                        console.log("Unknown CDN Types:", response.unknown_cdn_types); // 记录未知 CDN
                    } else {
                        showMessage("获取播放地址失败！"); // 未返回 URL 或错误时的提示
                    }
                },
                error: function (xhr) { // 请求失败回调
                    showMessage("请求失败: " + xhr.statusText); // 显示请求失败提示
                },
                complete: function () { // 请求完成（成功或失败）后执行
                    $("#btnParse").prop('disabled', false); // 重新启用解析按钮
                    $("#bid").val(''); // 清空输入框
                }
            });
        }

        // 新增的解析函数，使用代理接口
        function updateStream2(id) {
            // 处理输入的 ID，提取纯 ID 部分
            let cleanId = id;
            if (id.includes('huya.com')) {
                // 提取 URL 中的 ID（例如 https://www.huya.com/abc123 -> abc123）
                const match = id.match(/\/([^\/]+)$/);
                if (match) {
                    cleanId = match[1];
                } else {
                    showMessage("无效的虎牙 URL 格式！");
                    return;
                }
            }

            if (!cleanId) {
                showMessage("虎牙 ID 不能为空！");
                return;
            }

            const cdn = $("#cdn-group .btn.active").data('cdn'); // 获取选中的 CDN
            const media = $("#media-group .btn.active").data('media'); // 获取选中的媒体类型
            // 禁用解析按钮
            $("#btnParse2").prop('disabled', true);
            $.ajax({
                url: 'proxy.php', // 使用本地代理接口
                method: 'GET', // 使用 GET 方法
                data: { id: cleanId , action: 'proxy'}, // 传递 ID 参数
                dataType: 'json', // 预期返回 JSON 数据
                success: function (response) { // 请求成功回调
                    console.log("Response:", response); // 记录返回数据
                    if (response[media] && response[media][cdn]) { // 检查是否有对应的 CDN 和媒体类型链接
                        const playUrl = response[media][cdn]; // 获取播放链接
                        dp.switchVideo({ url: playUrl, type: 'auto' }); // 切换播放器视频
                        dp.play(); // 自动播放
                        showMessage("播放地址已更新并开始播放！"); // 显示成功提示
                        // 保存到最近播放
                        saveRecentPlay(cleanId);
                    } else {
                        showMessage("未找到对应的播放地址！"); // 无有效链接时的提示
                    }
                },
                error: function (xhr) { // 请求失败回调
                    showMessage("请求失败: " + xhr.statusText); // 显示请求失败提示
                },
                complete: function () { // 请求完成（成功或失败）后执行
                    $("#btnParse2").prop('disabled', false); // 重新启用解析按钮
                    $("#bid").val(''); // 清空输入框
                }
            });
        }

        // 从本地存储加载已保存的房间数据
        let roomData = JSON.parse(localStorage.getItem('roomData')) || {}; // 如果无数据则为空对象

        // 将本地保存的房间添加到自动补全列表
        function addOption() {
            for (let roomNumber in roomData) { // 遍历本地房间数据
                if (roomData.hasOwnProperty(roomNumber)) {
                    newdata.push(`${roomNumber}<br>${roomData[roomNumber]}`); // 添加到自动补全数据源
                }
            }
            autoCompleteJS.data.src = newdata; // 更新自动补全数据
        }
        addOption(); // 执行添加本地数据

        // 从服务器获取房间数据并添加到自动补全列表
        function getRooms() {
            sendRequest('get', 'action=get', function(response) { // 发送获取请求
                const rooms = response.data; // 获取返回的房间列表
                const autoCompleteData = rooms.map(room => `${room.room_id}<br>${room.room_name}`); // 格式化数据
                newdata = [...newdata, ...autoCompleteData]; // 合并本地和服务器数据
                autoCompleteJS.data.src = newdata; // 更新自动补全数据源
            });
        }
        getRooms(); // 执行获取服务器数据

        // 保存最近播放记录
        function saveRecentPlay(id) {
            let recentPlays = JSON.parse(localStorage.getItem('recentPlays')) || [];
            // 移除重复的 ID（如果存在）
            recentPlays = recentPlays.filter(item => item !== id);
            // 添加到数组开头
            recentPlays.unshift(id);
            // 最多保存 5 个记录
            if (recentPlays.length > 5) {
                recentPlays = recentPlays.slice(0, 5);
            }
            // 保存到 localStorage
            localStorage.setItem('recentPlays', JSON.stringify(recentPlays));
            // 更新按钮显示
            displayRecentPlays();
        }

        // 显示最近播放按钮
        function displayRecentPlays() {
            const recentPlays = JSON.parse(localStorage.getItem('recentPlays')) || [];
            const container = $('#recentPlayButtons');
            container.empty(); // 清空现有按钮
            recentPlays.forEach(id => {
                const button = $(`<button class="btn btn-outline-secondary">${id}</button>`);
                button.click(() => {
                    $('#bid').val(id); // 填入输入框
                    updateStream(id); // 触发解析
                });
                container.append(button);
            });
        }

        // 页面加载时显示最近播放记录
        displayRecentPlays();

        // 为 #bid 输入框绑定回车键事件
        $("#bid").keydown(function (e) {
            if (e.keyCode == 13) { // 检测回车键
                $('#btnParse').trigger("click"); // 触发解析按钮点击事件
            }
        });

        // 解析按钮点击事件
        $("#btnParse").click(function () {
            const bid = $("#bid").val(); // 获取输入的虎牙 ID
            if (bid) { // 如果输入不为空
                updateStream(bid); // 调用更新播放流函数
            } else {
                showMessage("请输入虎牙ID！"); // 提示用户输入ID
            }
        });

        // 新增解析按钮点击事件
        $("#btnParse2").click(function () {
            const bid = $("#bid").val(); // 获取输入的虎牙 ID
            if (bid) { // 如果输入不为空
                updateStream2(bid); // 调用新解析函数
            } else {
                showMessage("请输入虎牙ID！"); // 提示用户输入ID
            }
        });

        // 保存房间的通用函数
        function saveRoom(action) {
            const roomId = $("#saveRoomId").val(); // 获取输入的虎牙 ID
            const roomName = $("#saveRoomName").val(); // 获取输入的房间名
            if (!roomId || !roomName) { // 检查输入是否为空
                showMessage("请填写虎牙ID和房间名！");
                return;
            }

            if (action === 'local') { // 本地保存
                roomData[roomId] = roomName; // 将 ID 和房间名保存到对象
                localStorage.setItem('roomData', JSON.stringify(roomData)); // 保存到本地存储
                newdata.push(`${roomId}<br>${roomName}`); // 添加到自动补全列表
                autoCompleteJS.data.src = newdata; // 更新自动补全数据
                showMessage("已保存到本地！");
            } else if (action === 'network') { // 网络保存
                const data = `room_id=${encodeURIComponent(roomId)}&room_name=${encodeURIComponent(roomName)}`; // 格式化请求数据
                sendRequest('add', data, function(response) { // 发送添加请求
                    newdata.push(`${roomId}<br>${roomName}`); // 添加到自动补全列表
                    autoCompleteJS.data.src = newdata; // 更新自动补全数据
                    showMessage(response.message || "房间信息已保存到网络！");
                });
            }
            $("#saveRoomId").val(''); // 清空 ID 输入框
            $("#saveRoomName").val(''); // 清空房间名输入框
        }

        // 绑定保存按钮点击事件
        $("#btnSaveLocal").click(function () {
            saveRoom('local'); // 调用本地保存
        });
        $("#btnSaveNetwork").click(function () {
            saveRoom('network'); // 调用网络保存
        });

        // CDN 和媒体类型按钮组的点击事件
        $("#cdn-group .btn").click(function() {
            $("#cdn-group .btn").removeClass("active"); // 移除其他按钮的选中状态
            $(this).addClass("active"); // 添加当前按钮的选中状态
        });

        $("#media-group .btn").click(function() {
            $("#media-group .btn").removeClass("active"); // 移除其他按钮的选中状态
            $(this).addClass("active"); // 添加当前按钮的选中状态
        });
    });
</script>
</body>
</html>