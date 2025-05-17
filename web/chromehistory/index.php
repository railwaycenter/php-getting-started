<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>书签管理 v1.0.15</title> <!-- 更新版本号为 v1.0.15 -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>书签管理</h1>

    <div id="message"></div>

    <!-- API Token 输入区域 -->
    <div class="form-group">
        <label for="api_token">API Token:</label>
        <input type="text" id="api_token" name="api_token" placeholder="请输入 API Token" required>
        <button onclick="saveApiToken()">保存 Token</button>
    </div>
    
    <!-- 黑名单管理区域，默认折叠 -->
    <details class="blacklist-group">
        <summary><h2>黑名单管理</h2></summary>
        <table id="blacklist-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>黑名单词</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </details>

    <div class="search-group">
        <input type="text" id="search" placeholder="搜索 URL 或标题">
        <button onclick="searchBookmarks()">搜索</button>
        <button onclick="resetSearch()">重置</button>
    </div>

    <h2>新增书签</h2>
    <div class="form-group">
        <input type="text" id="add-url" placeholder="URL" required>
        <input type="text" id="add-title" placeholder="标题" required>
        <input type="datetime-local" id="add-date" required>
        <label><input type="checkbox" id="add-isBookmarked"> 是否书签</label>
        <button onclick="addBookmark()">添加</button>
    </div>

    <div class="bookmark-header">
        <h2>书签列表</h2>
        <button class="batch-delete-btn" onclick="confirmBatchDelete()">批量删除</button>
    </div>
    <table id="bookmark-table">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all" onclick="toggleSelectAll()"></th>
                <th>ID</th>
                <th>URL</th>
                <th>标题</th>
                <th>日期</th>
                <th>是否书签</th>
                <th>创建时间</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

        <div class="pagination">
            <span>每页显示: </span>
            <select id="per-page">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="30">30</option> <!-- 默认值由脚本动态设置 -->
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
    </div>

    <script src="scripts.js" defer></script>
</body>
</html>