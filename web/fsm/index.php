<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>种子列表管理系统</title>
    <style>
        body
        {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }

        a {
            text-decoration: none!important;
            /*color: var(--el-color-primary)*/
        }

        a:hover {
            /*color: var(--el-color-primary-light-3)*/
        }


        .container
        {
            max-width: 1200px;
            margin: 0 auto;
        }

        .controls
        {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-filters
        {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-item
        {
            display: flex;
            flex-direction: column;
        }

        .filter-item label
        {
            margin-bottom: 5px;
            color: #666;
        }

        .filter-item select, .filter-item input
        {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /*#searchKeyword{*/
        /*    width: 100%; !* 让输入框填满容器 *!*/
        /*    padding: 5px;*/
        /*    padding-right: 20px; !* 为了留出空间给清空按钮 *!*/
        /*}*/

        #clearKeyword {
            position: absolute;
            padding: 5px;
            right: 5px; /* 按钮靠右 */
            top: 50%;
            transform: translateY(-50%); /* 垂直居中 */
            background: transparent;
            border: none;
            font-size: 26px; /* 调整大小 */
            color: #555; /* 默认颜色，灰色 */
            cursor: pointer;
            outline: none; /* 去掉点击后的边框 */
            display: none; /* 默认隐藏 */
        }

        #clearKeyword:hover {
            color: #000; /* 悬停时变为黑色 */
        }

        #clearKeyword:active {
            color: #007BFF; /* 点击时变为蓝色 */
        }

        /* 当输入框有内容时，显示清空按钮 */
        input#searchKeyword:not(:placeholder-shown) + #clearKeyword {
            display: block;
        }

        #clearTokenButton, #clearSearchWordButton {
            position: absolute;
            padding: 5px;
            right: 5px; /* 按钮靠右 */
            top: 50%;
            transform: translateY(-50%); /* 垂直居中 */
            background: transparent;
            border: none;
            font-size: 26px; /* 调整大小 */
            color: #555; /* 默认颜色，灰色 */
            cursor: pointer;
            outline: none; /* 去掉点击后的边框 */
            display: none; /* 默认隐藏 */
        }

        /* 当按钮悬停时 */
        #clearTokenButton:hover, #clearSearchWordButton:hover {
            color: #ff0000; /* 悬停时变为红色 */
        }

        /* 当输入框有内容时，显示清空按钮 */
        input#apiToken:not(:placeholder-shown) + #clearTokenButton {
            display: block;
        }

        input#searchWord:not(:placeholder-shown) + #clearSearchWordButton {
            display: block;
        }

        #loadDataButton,#loadMoreButton {
            padding: 8px 15px;
            margin-left: 20px; /* 与输入框保持间距 */
            cursor: pointer;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #2ecc71;
            font-size: 14px;
            color: white;
            white-space: nowrap; /* 禁止换行 */
        }

        /* 设置按钮容器 */
        #addToRSSButton {
            display: inline-block;
            width: 20px; /* 设置按钮的宽度 */
            height: 12px; /* 设置按钮的高度 */
            cursor: pointer; /* 鼠标悬停时显示为手型 */
            transition: transform 0.3s ease, fill 0.3s ease; /* 设置过渡动画 */
            padding: 0; /* 去掉按钮内边距 */
            border: none; /* 去掉按钮边框 */
            background: transparent; /* 去掉按钮背景 */
        }

        /* 设置 SVG 的大小和形状 */
        #addToRSSButton svg {
            width: 100%;
            height: 100%;
            fill: #FF3366; /* 初始颜色为红色 */
        }

        /* 鼠标悬停时，按钮放大 */
        #addToRSSButton:hover {
            transform: scale(1.5);
        }

        /* 鼠标点击时，按钮缩小 */
        #addToRSSButton:active svg {
            transform: scale(0.9);
            fill: #FF6699; /* 点击时改变颜色 */
        }



        .torrent-list
        {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .torrent-item
        {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;/* 垂直居中对齐 */
        }

        .torrent-item:hover
        {
            background-color:#ecf5ff;
        }

        .torrent-item:last-child
        {
            border-bottom: none;
        }

        .title
        {
            flex: 1; /* 使标题占据剩余空间 */
            margin-right: 10px;/* 右侧间距 */
            max-width: 600px;
        }

        .stats
        {
            display: flex;
            gap: 20px;/* 各项之间的间距 */
            color: #666;
            align-items: center; /* 垂直居中对齐 */
        }

        .count-info{

            font-weight: bold;
            width:100px;
        }

        .upload-count
        {
            color: #2ecc71;
        }

        .download-count
        {
            color: #ff0000;
        }

        /* 添加文本对齐 */
        .torrent-item .stats span {
            text-align: left; /* 左对齐 */
            min-width: 120px; /* 设置最小宽度，确保对齐 */
            max-width: 120px; /* 设置最小宽度，确保对齐 */
        }

        .pagination
        {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }

        .pagination button
        {
            padding: 8px 15px;
            border: none;
            background: #2ecc71;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .pagination button:disabled
        {
            background: #ccc;
            cursor: not-allowed;
        }

        .token-input
        {
            width:100%;
            margin-bottom: 20px;
            text-align: center;
            display: flex; /* 使子元素按行排列 */
            align-items: center; /* 垂直居中 */
            justify-content: space-between;
        }

        /* 输入框容器，position: relative 以便于定位清空按钮 */
        .token-input > div {
            position: relative;
            display: inline-block;
            width: 100%;
            margin: 5px;
        }

        .token-input input
        {
            padding: 8px;
            padding-right: 30px; /* 为清空按钮留空间 */
            width: 100%;
            /*margin-right: 10px;*/
            box-sizing: border-box; /* 包括 padding 和 border */
            /*flex-grow: 1; !* 输入框占用可用宽度 *!*/
        }

        button
        {
            padding: 8px 15px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover
        {
            background-color: #27ae60;
        }

        .thumbnail-container {
            position: relative; /* 确保子元素可以定位 */
            display: inline-block;
            /*overflow: visible; !* 允许图片超出容器显示 *!*/
            /*overflow: hidden; !* 防止图片超出容器时未悬停就显示出来 *!*/
        }

        .cover {
            width: 30px; /* 初始缩略图宽度 */
            height: 30px; /* 初始缩略图高度 */
            object-fit: cover; /* 保持图片比例 */
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* 平滑过渡 */
        }

        /* 悬浮大图样式 */
        .thumbnail-container::after {
            content: ""; /* 创建伪元素 */
            position: absolute; /* 绝对定位 */
            top: 0; /* 与原图对齐 */
            left: 35px; /* 与原图对齐 */
            width: 150px; /* 悬浮图的宽度 */
            height: 150px; /* 悬浮图的高度 */
            /*    width: auto; !* 自动调整宽度以显示完整图片 *!*/
            /*    height: auto; !* 自动调整高度以显示完整图片 *!*/
            /*    max-width: 400px; !* 限制放大图片的最大宽度 *!*/
            /*    max-height: 400px; !* 限制放大图片的最大高度 *!*/
            border: 1px #aaaaaa solid;
            border-radius: 10px;
            z-index: 1000; /* 保证悬停时图片在最前面 */
            background-image: var(--data-cover); /* 使用原图作为背景 */
            background-size: cover; /* 保持背景图比例 */
            background-position: center; /* 背景居中 */
            opacity: 0; /* 初始透明度为0 */
            transition: opacity 0.3s ease; /* 添加平滑过渡效果 */
            /*z-index: 0; !* 确保在小图下面 *!*/
            pointer-events: none; /* 防止遮挡下层元素 */
        }

        .thumbnail-container:hover .cover {
            /*transform: scale(1.1); !* 悬停时小图轻微放大 *!*/
        }

        .thumbnail-container:hover::after {
            opacity: 1; /* 悬停时显示悬浮图 */
        }

        /* 模态框样式 */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        /* 模态框内容样式 */
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 300px;
            text-align: center;
            box-sizing: border-box; /* 包含内边距和边框在内的宽度计算 */
            border-radius: 8px; /* 圆角边框 */
            position: relative; /* 相对定位，为了定位关闭按钮 */
        }

        /* 关闭按钮样式 */
        .close {
            position: absolute;  /* 绝对定位 */
            top: 10px;           /* 距离顶部10px */
            right: 10px;         /* 距离右侧10px */
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s; /* Smooth transition for hover effect */
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }



        .loading
        {
            text-align: center;
            padding: 20px;
        }

        .error
        {
            color: red;
            text-align: center;
            padding: 20px;
        }


        /*white-space: nowrap; !* 禁止内容换行 *!*/
        /*overflow: hidden; !* 隐藏超出部分内容 *!*/
        /*text-overflow: ellipsis; !* 超出部分显示省略号 *!*/
    </style>
</head>
<body>
<div class="container">
    <!-- 用于输入 API Token 的区域 -->
    <div class="token-input">
        <div  style="position: relative; display: inline-block; width: 100%;">
            <input type="text" id="apiToken" placeholder="请输入 API Token" style="width: 100%; padding-right: 0px;">
            <button id="clearTokenButton">×</button>
        </div>

        <div>
            <input type="text" id="searchWord" placeholder="请输入搜索关键词">
            <button id="clearSearchWordButton">×</button>
        </div>

        <button  id="loadDataButton">加载数据</button>
        <!-- 下一页按钮 -->
        <button id="loadMoreButton">加载更多</button>

    </div>

    <!-- 控制台和筛选部分 -->
    <div class="controls">
        <div class="search-filters">
            <!-- 搜索关键词 -->
            <div class="filter-item">
                <label>搜索关键词</label>
                <div  style="position: relative; display: inline-block; width: 95%;">
                    <input type="text" id="searchKeyword" placeholder="输入关键词" oninput="applyFilters()" style="width: 100%; padding-right: 0px;">
                    <button id="clearKeyword" onclick="clearKeyword()">×</button>
                </div>
            </div>
            <!-- 排序方式 -->
            <div class="filter-item">
                <label>排序方式</label>
                <select id="sortBy" onchange="applyFilters()">
                    <option value="download">下载人数（降序）</option>
                    <option value="upload">上传人数（降序）</option>
                    <option value="publishTime">发布时间（降序）</option>
                    <option value="size">文件大小（降序）</option>
                    <option value="name">名称（升序）</option>
                </select>
            </div>
            <!-- 每页显示数量 -->
            <div class="filter-item">
                <label>每页显示</label>
                <select id="pageSize" onchange="changePageSize(this.value)">
                    <option value="10">10条</option>
                    <option value="20">20条</option>
                    <option value="30" selected>30条</option>
                    <option value="50">50条</option>
                </select>
            </div>
            <!-- 类型筛选 -->
            <div class="filter-item">
                <label>类型筛选</label>
                <select id="typeFilter" onchange="applyFilters()">
                    <option value="0">全部</option>
                    <option value="1">视频</option>
                    <option value="2">音频</option>
                    <option value="3">文档</option>
                </select>
            </div>
        </div>
<!--        <button onclick="applyFilters()">应用筛选</button>-->
    </div>

    <!-- 动态生成的种子列表 -->
    <div class="torrent-list" id="torrentList">
        <!-- 列表内容 -->
    </div>

    <!-- 分页控件 -->
    <div class="pagination" id="pagination">
        <!-- 分页内容 -->
    </div>
</div>



<!-- Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeMessage()">&times;</span>
        <p id="modal-message"></p>
    </div>
</div>



<script>
    /**
     * 全局状态，用于跟踪分页、筛选、数据等。
     */
    const state = {
        data: [], // 原始数据
        filteredData: [], // 筛选后的数据
        currentPage: 1, // 当前页码
        pageSize: 10, // 每页显示的条目数
        totalPages: 1, // 总页数
        keyword: '', // 搜索关键词
        keywords: '', // 提交api时的搜索关键词
        type: '0', // 类型筛选
        sortBy: 'publishTime', // 排序依据
        loading: false, // 是否正在加载数据
        token: '', // API Token
    };

    // Show the message in the modal
    function showMessage(message) {
        const modal = document.getElementById("myModal");
        document.getElementById("modal-message").innerText = message;
        modal.style.display = "block";

        // Automatically close the modal after 2 seconds (2000 milliseconds)
        // setTimeout(() => {
        //     closeMessage();
        // }, 2000);
    }

    // Close the modal
    function closeMessage() {
        const modal = document.getElementById("myModal");
        modal.style.display = "none";
    }

    // Close the modal if clicked outside of the modal content
    window.onclick = function(event) {
        const modal = document.getElementById("myModal");
        if (event.target === modal) {
            closeMessage();
        }
    }

    /**
     * 从服务器获取种子数据
     */
    function fetchTorrents() {
        state.loading = true;
        // updateUI();

        const url = 'api.php';
        const params = {
            type: state.type, // 资源类型
            keyword: state.keywords, // 搜索关键词
            page: state.currentPage, // 当前页码
            pageSize: state.pageSize, // 每页大小
            action:'getInfo'
        };

        // 发起 POST 请求到后端
        fetch(url, {
            method: 'POST',
            headers: {
                'APITOKEN': state.token, // 将输入的 Token 作为请求头传递
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(params) // 将参数作为 JSON 传递
        }).then(response => response.json())
        .then(data => {
            if (!data.success) throw new Error(data.msg); // 如果返回错误，抛出异常
            let newTorrents = data.data.list;
            state.data = mergeUniqueTorrents(state.data, newTorrents);
                // state.data = data.data.list; // 保存数据
            state.totalPages = data.data.maxPage; // 更新总页数
            state.filteredData = [...state.data]; // 初始化筛选后的数据为原始数据
            applyFilters(); // 应用筛选和排序
        })
        .catch(error => {
            // 显示错误信息
            document.getElementById('torrentList').innerHTML = `
                  <div class="error">获取数据失败：${error.message}</div>
              `;
        })
        .finally(() => {
            state.loading = false; // 结束加载状态
            updateUI(); // 确保界面更新
        });
    }

    // 合并两个数组，并去重（通过文件哈希或其他唯一字段）
    function mergeUniqueTorrents(existingTorrents, newTorrents) {
        const existingIds = new Set(existingTorrents.map(t => t.tid));  // 用哈希值去重
        return [
            ...existingTorrents,
            ...newTorrents.filter(t => !existingIds.has(t.tid))
        ];
    }


    /**
     * 对数据进行筛选和排序
     */
    function applyFilters() {
        state.keyword = document.getElementById('searchKeyword').value.trim();
        state.sortBy = document.getElementById('sortBy').value;
        state.pageSize = parseInt(document.getElementById('pageSize').value);
        state.type = document.getElementById('typeFilter').value;
        // state.currentPage = 1; // 重置为第一页

        // 筛选数据
        let filteredData = state.data.filter(torrent => {
            const title = torrent.title.toLowerCase();
            const keyword = state.keyword.toLowerCase();
            const type = state.type;

            // 关键词匹配
            if (state.keyword && !title.includes(keyword)) {
                return false;
            }

            // 类型匹配
            if (type !== '0' && torrent.type.id !== parseInt(type)) {
                return false;
            }

            return true;
        });

        // 排序数据
        filteredData.sort((a, b) => {
            switch (state.sortBy) {
                case 'download':
                    return b.peers.download - a.peers.download; // 按下载人数降序
                case 'upload':
                    return b.peers.upload - a.peers.upload; // 按上传人数降序
                case 'publishTime':
                    return new Date(b.createdTs * 1000) - new Date(a.createdTs * 1000); // 按发布时间降序
                case 'size':
                    return parseFloat(b.fileRawSize) - parseFloat(a.fileRawSize); // 按大小降序
                case 'name':
                    return a.title.localeCompare(b.title); // 按名称升序
                default:
                    return 0;
            }
        });

        // 更新筛选后的数据
        state.filteredData = filteredData;
        // console.log(filteredData);
        state.totalPages = Math.ceil(filteredData.length / state.pageSize); // 更新总页数

        // 默认第一页
        // state.currentPage = 1;

        // 更新界面
        updateUI();
    }

    /**
     * 对数据进行排序
     */
    function changePageSize(size) {
        state.pageSize = parseInt(size);
        state.currentPage = 1; // 重置到第一页
        console.log('current pageSize:'  + state.pageSize);
        applyFilters(); // 更新筛选结果和分页
    }

    /**
     * 对数据进行排序
     */
    // function sortData() {
    //     state.data.sort((a, b) => {
    //         switch (state.sortBy) {
    //             case 'download':
    //                 return b.peers.download - a.peers.download; // 按下载量降序
    //             case 'publishTime':
    //                 return new Date(b.createdTs * 1000) - new Date(a.createdTs * 1000); // 按发布时间降序
    //             case 'size':
    //                 return parseFloat(b.fileRawSize) - parseFloat(a.fileRawSize); // 按大小降序
    //             case 'name':
    //                 return a.title.localeCompare(b.title); // 按名称升序
    //             default:
    //                 return 0;
    //         }
    //     });
    // }

    /**
     * 更新界面显示
     */
    function updateUI() {
        const torrentListElement = document.getElementById('torrentList');
        const paginationElement = document.getElementById('pagination');

        // 如果正在加载，显示加载提示
        if (state.loading) {
            torrentListElement.innerHTML = '<div class="loading">加载中...</div>';
            return;
        }

        //数据小于每页大小则默认第一页
        if(state.filteredData.length<state.pageSize)
        {
            state.currentPage = 1;
        }
        // 更新种子列表内容
        const startIndex = (state.currentPage - 1) * state.pageSize;
        const paginatedData = state.filteredData.slice(startIndex, startIndex + state.pageSize);



        torrentListElement.innerHTML = paginatedData.map(torrent => `
            <div class="torrent-item">
                <div class="thumbnail-container" style="--data-cover: url(${torrent.cover});">
                    <img src="${torrent.cover}" alt="${torrent.title}" class="cover">
                </div>
                <div class="title">
                    ${torrent.title}
                    <span style="color:#67c23a">${torrent.status.name}</span>:${torrent.status.endAt ? `<span style="color:#409eff"> ${Math.round((torrent.status.endAt - Date.now() / 1000) / (60 * 60)) - 1}</span>` : ''}
                    <a href="https://fsm.name/Torrents/details?tid=${torrent.tid}" target="_blank">${torrent.tid}</a>
                </div>
                <div class="stats">
                    <span class="count-info">
                        <span class="upload-count">U:${torrent.peers.upload}</span> / <span class="download-count">D: ${torrent.peers.download}</span>
                        <span class=""><button id="addToRSSButton" onclick="addToRSS(${torrent.tid})"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
    </svg></button></span>
                    </span>
                    <span>大小: ${torrent.fileSize}</span>
                    <span>发布时间: ${new Date(torrent.createdTs * 1000).toLocaleDateString()}</span>
                </div>
            </div>
        `).join('');

        // 更新分页控件内容
        paginationElement.innerHTML = `
            <button onclick="changePage(1)" ${state.currentPage === 1 ? 'disabled' : ''}>首页</button>
            <button onclick="changePage(${state.currentPage - 1})" ${state.currentPage === 1 ? 'disabled' : ''}>上一页</button>
            <span>第 ${state.currentPage} / ${Math.ceil(state.filteredData.length / state.pageSize)} 页</span>
            <button onclick="changePage(${state.currentPage + 1})" ${state.currentPage === Math.ceil(state.filteredData.length / state.pageSize) ? 'disabled' : ''}>下一页</button>
            <button onclick="changePage(${Math.ceil(state.filteredData.length / state.pageSize)})" ${state.currentPage === Math.ceil(state.filteredData.length / state.pageSize) ? 'disabled' : ''}>末页</button>
        `;
    }

    /**
     * 加载数据，需输入 Token
     */
    function loadData() {
        const apiTokenInput = document.getElementById('apiToken');
        const searchWordInput = document.getElementById('searchWord');
        state.token = apiTokenInput.value.trim();
        state.keywords = searchWordInput.value.trim();

        if (!state.token) {
            showMessage('请输入 API Token');
            return;
        }

        if(state.keywords)
        {
            state.data = [];
            state.filteredData = [];
            console.log('have keywords');
        }

        fetchTorrents();
        // state.keywords = '';
        // searchWordInput.value = '';
    }

    /**
     * 应用筛选条件
     */
    // function applyFilters() {
    //     state.keyword = document.getElementById('searchKeyword').value.trim();
    //     state.sortBy = document.getElementById('sortBy').value;
    //     state.pageSize = parseInt(document.getElementById('pageSize').value);
    //     state.type = document.getElementById('typeFilter').value;
    //     state.currentPage = 1; // 重置为第一页
    //     fetchTorrents(); // 重新加载数据
    // }

    /**
     * 切换分页
     */
    function changePage(page) {
        console.log('current page:' + page);
        if (page < 1 || page > state.totalPages) return;
        state.currentPage = page;
        applyFilters(); // 更新界面
    }

    /**
     * 格式化文件大小
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // 事件监听
    document.getElementById('apiToken').addEventListener('keypress', function (e)
    {
        if (e.key === 'Enter')
        {
            loadData();
        }
    });

    // 事件监听
    document.getElementById('searchWord').addEventListener('keypress', function (e)
    {
        if (e.key === 'Enter')
        {
            loadData();
        }
    });

    document.getElementById('searchKeyword').addEventListener('keypress', function (e)
    {
        if (e.key === 'Enter')
        {
            applyFilters();
        }
    });

    /**
     * 清空输入框
     */
    function clearKeyword() {
        // 清空输入框
        document.getElementById('searchKeyword').value = '';
        // 更新状态并应用筛选
        state.keyword = '';
        applyFilters();
    }

    // 发送 POST 请求，将种子添加到个人 RSS
    function addToRSS(torrentId) {
        const url = 'api.php';  // 调用 api.php 接口
        const params = {
            torrent_id: torrentId, // 传递种子 ID
            action: 'addListMySelfRss',// 通用 action 字段
        };


        fetch(url, {
            method: 'POST',
            headers: {
                'APITOKEN': state.token, // 将输入的 Token 作为请求头传递
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(params)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(`种子已加入个人 RSS：${torrentId}`);
            } else {
                showMessage(`加入 RSS 失败：${data.msg}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('加入 RSS 失败，请稍后再试！');
        });
    }

    // 点击 "加载更多" 按钮时，加载下一页数据
    document.getElementById('loadMoreButton').addEventListener('click', function() {
        if (state.currentPage < 10) {
            state.currentPage++;
            fetchTorrents();
        } else {
            showMessage('没有更多的种子了！');
        }
    });


    document.addEventListener('DOMContentLoaded', function () {
        const apiTokenInput = document.getElementById('apiToken');
        const searchWordInput = document.getElementById('searchWord');
        const loadButton = document.getElementById('loadDataButton');
        const clearTokenButton = document.getElementById('clearTokenButton'); // 新增清除按钮
        const clearSearchWordButton = document.getElementById('clearSearchWordButton'); // 新增清除按钮

        // 从本地缓存加载 apiToken
        const cachedApiToken = localStorage.getItem('apiToken');
        if (cachedApiToken) {
            apiTokenInput.value = cachedApiToken; // 自动填充
            loadData(cachedApiToken); // 自动加载数据
        }

        // 加载数据
        loadButton.addEventListener('click', function () {
            const apiToken = apiTokenInput.value.trim();
            const searchWord = searchWordInput.value.trim();
            if (apiToken) {
                localStorage.setItem('apiToken', apiToken); // 保存到缓存
                loadData(apiToken); // 调用加载函数
            } else {
                showMessage('请输入有效的 API Token');
            }
        });

        // 清除缓存的 API Token
        clearTokenButton.addEventListener('click', function () {
            //localStorage.removeItem('apiToken'); // 从缓存移除
            apiTokenInput.value = ''; // 清空输入框
        });

        clearSearchWordButton.addEventListener('click', function () {
            searchWordInput.value = ''; // 清空输入框
        });

    });
</script>
</body>
</html>
