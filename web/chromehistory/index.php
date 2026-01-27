<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>书签管理 v1.0.22</title> <!-- 更新版本号为 v1.0.22 -->
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="app-layout">
        <!-- 侧边栏：常驻控制面板 -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1>书签管理</h1>
                <button id="sidebar-toggle" title="折叠/展开侧边栏">
                    <span class="toggle-icon">❮</span>
                </button>
            </div>

            <div id="message"></div>

            <!-- API Token -->
            <section class="sidebar-section" title="API Token">
                <h3 class="section-title"><span class="icon">🔑</span> <span class="text">Token</span></h3>
                <div class="compact-input-group">
                    <input type="password" id="api_token" name="api_token" placeholder="输入 Token">
                    <button class="primary" onclick="saveApiToken()">保存</button>
                </div>
            </section>

            <!-- 搜索过滤 -->
            <section class="sidebar-section" title="搜索过滤">
                <h3 class="section-title"><span class="icon">🔍</span> <span class="text">搜索</span></h3>
                <div class="compact-form">
                    <select id="search-type">
                        <option value="keyword">关键词</option>
                        <option value="date">书签日期</option>
                        <option value="created_at">创建日期</option>
                    </select>
                    <input type="text" id="search" placeholder="搜索 URL 或标题">

                    <div id="date-range-inputs" style="display: none;">
                        <input type="date" id="start-date">
                        <input type="date" id="end-date">
                        <div class="quick-dates">
                            <button type="button" class="quick-date-btn" onclick="setQuickDate('today')">今日</button>
                            <button type="button" class="quick-date-btn" onclick="setQuickDate('yesterday')">昨日</button>
                            <button type="button" class="quick-date-btn" onclick="setQuickDate('this_week')">本周</button>
                        </div>
                    </div>
                </div>
                <div class="sidebar-actions">
                    <button class="primary" onclick="searchBookmarks()">立即搜索</button>
                    <button onclick="resetSearch()">重置</button>
                </div>
            </section>

            <!-- 新增面板 -->
            <section class="sidebar-section" title="新增书签">
                <details>
                    <summary>
                        <h3 class="section-title" style="display:inline;"><span class="icon">➕</span> <span class="text">新增</span></h3>
                    </summary>
                    <div class="compact-form" style="margin-top: 10px;">
                        <input type="text" id="add-url" placeholder="URL" required>
                        <input type="text" id="add-title" placeholder="标题" required>
                        <input type="datetime-local" id="add-date" required>
                        <label class="checkbox-label"><input type="checkbox" id="add-isBookmarked"> 书签</label>
                        <button class="primary" onclick="addBookmark()" style="width: 100%;">确认添加</button>
                    </div>
                </details>
            </section>

            <!-- 黑名单管理 -->
            <section class="sidebar-section" title="黑名单">
                <details class="blacklist-group">
                    <summary>
                        <h3 class="section-title" style="display:inline;"><span class="icon">🚫</span> <span class="text">黑名单</span></h3>
                    </summary>
                    <div style="margin-top: 10px; overflow-x: auto;">
                        <table id="blacklist-table" style="font-size: 0.75rem;">
                            <thead>
                                <tr>
                                    <th>词</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </details>
            </section>
        </aside>

        <!-- 主内容区：书签列表 -->
        <main class="main-content">
            <div class="card content-card">
                <div class="bookmark-header">
                    <h2>书签列表</h2>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <button id="theme-toggle" onclick="toggleTheme()" title="切换深色/浅色模式">
                            <span class="icon">🌓</span>
                        </button>
                        <button class="batch-delete-btn" onclick="confirmBatchDelete()">批量删除</button>
                    </div>
                </div>

                <div class="table-container">
                    <table id="bookmark-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all" onclick="toggleSelectAll()"></th>
                                <th class="pc-only">ID</th>
                                <th class="mobile-only">元数据</th>
                                <th>书签信息</th>
                                <th class="pc-only">日期</th>
                                <th class="pc-only">书签</th>
                                <th class="pc-only">创建时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="pagination">
                    <span>每页: </span>
                    <select id="per-page">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </main>
    </div>

    <!-- 移动端悬浮球 -->
    <button id="mobile-fab" onclick="toggleMobileSidebar()" title="设置">⚙️</button>
    <script src="scripts.js"></script>
</body>

</html>