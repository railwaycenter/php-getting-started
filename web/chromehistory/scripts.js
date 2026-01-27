// 从 index.php 中提取的 JavaScript 逻辑，添加初始化函数和优化
// 版本号 v1.0.22



// 缓存 DOM 引用以提升性能
const elements = {
    message: document.getElementById('message'),
    search: document.getElementById('search'),
    searchType: document.getElementById('search-type'),
    dateRangeInputs: document.getElementById('date-range-inputs'),
    startDate: document.getElementById('start-date'),
    endDate: document.getElementById('end-date'),
    perPage: document.getElementById('per-page'),
    addUrl: document.getElementById('add-url'),
    addTitle: document.getElementById('add-title'),
    addDate: document.getElementById('add-date'),
    addIsBookmarked: document.getElementById('add-isBookmarked'),
    selectAll: document.getElementById('select-all'),
    bookmarkTableBody: document.querySelector('#bookmark-table tbody'),
    blacklistTableBody: document.querySelector('#blacklist-table tbody')
};

// 从 LocalStorage 获取 api_token，优先于 DOM 元素
let api_token = localStorage.getItem('api_token') || document.getElementById('api_token').value.trim();

/**
 * 带有自动重试机制的 fetch 包装器
 * @param {string} url 
 * @param {object} options 
 * @param {number} retries 重试次数
 * @param {number} delay 延迟时间(ms)
 */
async function fetchWithRetry(url, options = {}, retries = 2, delay = 1000) {
    try {
        const response = await fetch(url, options);
        // 如果是 401 (未授权) 且仍有重试机会，且本地有 token 则尝试重试
        if (response.status === 401 && retries > 0 && api_token) {
            console.warn(`授权失败，正在进行重试... 剩余次数: ${retries}`);
            await new Promise(resolve => setTimeout(resolve, delay));
            return fetchWithRetry(url, options, retries - 1, delay);
        }
        return response;
    } catch (error) {
        if (retries > 0) {
            await new Promise(resolve => setTimeout(resolve, delay));
            return fetchWithRetry(url, options, retries - 1, delay);
        }
        throw error;
    }
}

// 保存 api_token 到 LocalStorage
function saveApiToken() {
    const tokenInput = document.getElementById('api_token').value.trim();
    if (!tokenInput) {
        showMessage('API Token 不能为空', true);
        return;
    }
    localStorage.setItem('api_token', tokenInput);
    api_token = tokenInput;
    showMessage('API Token 已保存');
    fetchData(); // 重新加载数据以应用新 token
    fetchBlacklist();
}

// 设置默认日期格式化为 datetime-local 所需的字符串 (支持 10位/13位/日期对象)
function formatLocalDateTime(date) {
    if (!date) return '';
    let d = new Date(date);
    // 自动适配 10 位时间戳 (秒) -> JS 所需毫秒
    if (typeof date === 'number' && date < 10000000000) {
        d = new Date(date * 1000);
    } else if (typeof date === 'string' && /^\d{10}$/.test(date)) {
        d = new Date(parseInt(date) * 1000);
    }

    if (isNaN(d.getTime())) return '';

    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// 检查并设置 addDate 的默认值
if (elements.addDate) {
    elements.addDate.value = formatLocalDateTime(new Date()); // 设置新增书签的默认时间为本地时间
} else {
    console.error('Element with id "add-date" not found');
}

function showMessage(message, isError = false, callback = null) {
    const modal = document.createElement('div');
    modal.className = `modal ${isError ? 'error' : 'success'}`; // 使用 modal 类并根据类型添加 error 或 success
    modal.tabIndex = -1; // 添加 tabIndex 使模态窗口可聚焦
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open'); // 锁定滚动条

    const closeBtn = document.createElement('button');
    closeBtn.className = 'modal-btn modal-cancel';
    closeBtn.textContent = '关闭';
    const closeAction = () => {
        overlay.remove();
        modal.remove();
        document.body.classList.remove('modal-open'); // 解锁滚动条
        if (callback) callback();
    };
    closeBtn.onclick = closeAction;
    overlay.onclick = closeAction;

    modal.innerHTML = `<p>${message}</p>`;
    const btnContainer = document.createElement('div');
    btnContainer.className = 'modal-buttons';
    btnContainer.appendChild(closeBtn);
    modal.appendChild(btnContainer);
    document.body.appendChild(modal);
    modal.focus(); // 设置焦点到模态窗口
    modal.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAction();
        }
    });

    // 3 秒后自动关闭
    setTimeout(() => {
        if (overlay && overlay.parentNode) {
            overlay.remove();
            document.body.classList.remove('modal-open'); // 解锁
        }
        if (modal && modal.parentNode) modal.remove();
        if (callback) callback();
    }, 3000);
}

function formatDate(timestamp) {
    if (!timestamp) return '-';
    let date;

    // 智能解析时间戳或日期字符串
    if (typeof timestamp === 'number') {
        // 如果小于 100 亿，认为是秒（10位），否则为毫秒（13位）
        date = new Date(timestamp < 10000000000 ? timestamp * 1000 : timestamp);
    } else if (typeof timestamp === 'string' && /^\d+$/.test(timestamp)) {
        const val = parseInt(timestamp);
        date = new Date(val < 10000000000 ? val * 1000 : val);
    } else {
        date = new Date(timestamp);
    }

    if (isNaN(date.getTime())) return timestamp;

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

function renderTable(data) {
    elements.bookmarkTableBody.innerHTML = '';
    // 建立全局缓存，供 editBookmarkById 使用
    window.bookmarksCache = {};
    data.bookmarks.forEach(bookmark => {
        window.bookmarksCache[bookmark.id] = bookmark;
        const tr = document.createElement('tr');
        tr.dataset.id = bookmark.id;
        const formattedDate = formatDate(bookmark.date);
        tr.innerHTML = `
            <td class="checkbox-cell" onclick="handleCheckboxCellClick(event, this)"><input type="checkbox" class="select-row" data-id="${bookmark.id}"></td>
            <!-- PC 模式独立的列 -->
            <td class="pc-only">${bookmark.id}</td>
            
            <!-- 移动端合并的行 -->
            <td class="mobile-only bookmark-meta-cell">
                <span class="meta-id">${bookmark.id}</span>
                <span class="meta-date" title="${formattedDate}">📅 ${formattedDate}</span>
                <span class="meta-status">${bookmark.isBookmarked === true ? '<span class="bookmark-star active" title="已收藏">⭐</span>' : '<span class="bookmark-star" title="未收藏">☆</span>'}</span>
            </td>

            <td class="bookmark-info-cell">
                <div class="bookmark-title">${bookmark.title}</div>
                <div class="url-action-row">
                    <div class="bookmark-url" title="${bookmark.url}"><a href="${bookmark.url}" target="_blank">${bookmark.url}</a></div>
                    <div class="mobile-only-actions">
                        <span class="mini-icon-btn" onclick="editBookmarkById(${bookmark.id})" title="修改">✏️</span>
                        <span class="mini-icon-btn delete" onclick="confirmDelete(${bookmark.id})" title="删除">🗑️</span>
                    </div>
                </div>
            </td>
            
            <!-- PC 模式独立的列 -->
            <td class="pc-only date-column" title="${formattedDate}"><span class="date-text">${formattedDate}</span></td>
            <td class="pc-only status-column">${bookmark.isBookmarked === true ? '<span class="bookmark-star active" title="已收藏">⭐</span>' : '<span class="bookmark-star" title="未收藏">☆</span>'}</td>
            <td class="pc-only created-at-column" title="${bookmark.created_at}">${bookmark.created_at}</td>
            <td class="pc-only">
                <button class="action-btn edit-btn" onclick="editBookmarkById(${bookmark.id})">修改</button>
                <button class="action-btn delete-btn" onclick="confirmDelete(${bookmark.id})">删除</button>
            </td>
        `;
        elements.bookmarkTableBody.appendChild(tr);
    });
    // 更新 <select> 的选中值以反映实际条数
    elements.perPage.value = data.itemsPerPage;
    renderPagination(data);
}

// 渲染黑名单列表（标签云模式）
function renderBlacklistTable(data) {
    // 目标容器：直接替换掉原来的表格结构
    const container = document.querySelector('.blacklist-group div');
    if (!container) return; // 容错

    const words = data.words ? data.words.split(',').filter(w => w) : [];

    let html = `<div class="tag-cloud">`;
    if (words.length === 0) {
        html += `<span class="tag-item" style="color:var(--text-muted);border-style:dashed;">(空)</span>`;
    } else {
        words.forEach(word => {
            html += `<span class="tag-item">${word}</span>`;
        });
    }
    html += `</div>`;

    // 添加快捷编辑按钮
    html += `<button class="tag-edit-btn" onclick="showEditBlacklistModal(${data.id}, '${encodeURIComponent(data.words)}')">✎ 编辑黑名单</button>`;

    container.innerHTML = html;
}

function renderPagination(data) {
    // 渲染分页控件，包括每页条数选择、页码导航和自定义跳转
    const pagination = document.querySelector('.pagination');
    const { totalPages, currentPage, itemsPerPage } = data;

    // 每页条数选择，默认值为 30，选项包括 5, 10, 20, 30, 50, 100
    let html = '<span>每页显示: </span><select id="per-page" onchange="updatePerPage(this.value)">';
    [5, 10, 20, 30, 50, 100].forEach(option => {
        html += `<option value="${option}" ${itemsPerPage == option ? 'selected' : ''}>${option}</option>`;
    });
    html += '</select>';

    if (totalPages > 1) {
        // 添加“上一页”按钮
        if (currentPage > 1) {
            html += `<a href="#" onclick="changePage(${currentPage - 1}); return false;">上一页</a>`;
        }

        // 计算分页范围，前后各显示 3 页
        const range = 3;
        const start = Math.max(1, currentPage - range);
        const end = Math.min(totalPages, currentPage + range);

        // 如果起始页大于 1，显示第 1 页和省略号
        if (start > 1) {
            html += `<a href="#" onclick="changePage(1); return false;">1</a>`;
            if (start > 2) html += '<span>...</span>';
        }

        // 渲染页码按钮
        for (let i = start; i <= end; i++) {
            html += `<a href="#" onclick="changePage(${i}); return false;" ${i == currentPage ? 'class="current"' : ''}>${i}</a>`;
        }

        // 如果结束页小于总页数，显示省略号和最后一页
        if (end < totalPages) {
            if (end < totalPages - 1) html += '<span>...</span>';
            html += `<a href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a>`;
        }

        // 添加“下一页”按钮
        if (currentPage < totalPages) {
            html += `<a href="#" onclick="changePage(${currentPage + 1}); return false;">下一页</a>`;
        }

        // 添加自定义跳转输入框和按钮按钮
        html += `
            <span>跳转到: </span>
            <input type="number" id="jump-page" min="1" max="${totalPages}" value="${currentPage}" 
                onkeypress="if(event.key === 'Enter') changePage(this.value)">
            <button onclick="changePage(document.getElementById('jump-page').value)">跳转</button>
        `;
    }

    pagination.innerHTML = html;
}

function fetchData() {
    // 移除 showMessage('加载中...', false) 以避免信息覆盖
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page') || 1;
    const perPage = urlParams.get('per_page') || 50; // 从 URL 获取 per_page，默认 50
    const search = urlParams.get('search') || '';
    const searchType = urlParams.get('search_type') || 'keyword';
    const startDate = urlParams.get('start_date') || '';
    const endDate = urlParams.get('end_date') || '';

    fetchWithRetry(`api.php?api_token=${encodeURIComponent(api_token)}&page=${page}&per_page=${perPage}&search=${encodeURIComponent(search)}&search_type=${searchType}&start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                renderTable(data);
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('数据加载失败: ' + error, true));
}

// 获取黑名单
function fetchBlacklist() {
    fetchWithRetry(`api.php?api_token=${encodeURIComponent(api_token)}&action=get_blacklist`)
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                renderBlacklistTable(data);
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('获取黑名单失败: ' + error, true));
}

function addBookmark() {
    const url = elements.addUrl.value.trim();
    const title = elements.addTitle.value.trim();
    const dateInput = elements.addDate ? elements.addDate.value : null;
    // 转换为 MySQL 兼容的 YYYY-MM-DD HH:MM:SS 格式
    const date = dateInput ? dateInput.replace('T', ' ') + ':00' : formatDate(new Date());
    const isBookmarked = elements.addIsBookmarked.checked;

    if (!url || !title) {
        showMessage('URL 和标题不能为空', true);
        return;
    }
    if (!url.match(/^https?:\/\/.+/)) {
        showMessage('请输入有效的 URL', true);
        return;
    }

    fetchWithRetry(`api.php?api_token=${encodeURIComponent(api_token)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', url, title, date, isBookmarked })
    })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                showMessage(data.message);
                fetchData();
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('请求失败: ' + error, true));
}

// 更新黑名单词
function updateBlacklistWords() {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open'); // 锁定

    const confirmDiv = document.createElement('div');
    confirmDiv.className = 'confirm-container';
    confirmDiv.tabIndex = -1;
    confirmDiv.innerHTML = `
        <p>确定更新黑名单吗？此操作将覆盖现有黑名单。</p>
        <div class="confirm-buttons">
            <button class="confirm-btn confirm-yes" onclick="confirmUpdateBlacklistWords(); document.body.classList.remove('modal-open'); document.querySelector('.modal-overlay').remove(); this.parentNode.parentNode.remove()">是</button>
            <button class="confirm-btn confirm-no" onclick="document.body.classList.remove('modal-open'); document.querySelector('.modal-overlay').remove(); this.parentNode.parentNode.remove()">否</button>
        </div>
    `;
    document.body.appendChild(confirmDiv);
    confirmDiv.focus();
    overlay.onclick = () => { document.body.classList.remove('modal-open'); overlay.remove(); confirmDiv.remove(); };
    confirmDiv.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.body.classList.remove('modal-open');
            overlay.remove(); // 联动清理遮罩层
            confirmDiv.remove();
        }
    });
}

function confirmUpdateBlacklistWords() {
    const modal = document.querySelector('.modal');
    const wordsInput = modal ? document.getElementById('edit-blacklist-words').value.trim() : '';
    const words = wordsInput.split('\n').map(w => w.trim()).filter(w => w).join(','); // 每行转为逗号分隔
    fetchWithRetry(`api.php?api_token=${encodeURIComponent(api_token)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_blacklist', id: 1, words })
    })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                showMessage(data.message);
                fetchBlacklist();
                const editModal = document.querySelector('.modal');
                if (editModal) editModal.remove(); // 保存后关闭编辑模态框
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('更新黑名单失败: ' + error, true));
}

function showEditModal(id, encodedUrl, encodedTitle, date, isBookmarked) {
    const url = decodeURIComponent(encodedUrl);
    const title = decodeURIComponent(encodedTitle);
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.tabIndex = -1; // 添加 tabIndex 使模态窗口可聚焦
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open'); // 锁定

    modal.innerHTML = `
        <h3>编辑书签</h3>
        <label>URL: <input type="text" id="edit-url" value="${url}" required></label>
        <label>标题: <input type="text" id="edit-title" value="${title}" required></label>
        <label>日期: <input type="datetime-local" id="edit-date" value="${formatLocalDateTime(date)}" required></label>
        <label class="checkbox-label"><input type="checkbox" id="edit-isBookmarked" ${isBookmarked ? 'checked' : ''}> 书签</label>
        <div class="modal-buttons">
            <button class="modal-btn modal-cancel" onclick="document.body.classList.remove('modal-open'); document.querySelector('.modal-overlay').remove(); this.parentNode.parentNode.remove()">取消</button>
            <button class="modal-btn modal-save" onclick="editBookmark(${id}); document.body.classList.remove('modal-open'); document.querySelector('.modal-overlay').remove(); this.parentNode.parentNode.remove()">保存</button>
        </div>
    `;
    document.body.appendChild(modal);
    // 监听回车保存
    const inputs = modal.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                editBookmark(id);
                document.body.classList.remove('modal-open');
                overlay.remove();
                modal.remove();
            }
        });
    });

    document.getElementById('edit-url').focus(); // 将焦点设置为 edit-url 输入框
    overlay.onclick = () => { document.body.classList.remove('modal-open'); overlay.remove(); modal.remove(); };
    modal.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.body.classList.remove('modal-open');
            overlay.remove();
            modal.remove();
        }
    });
}

// 编辑黑名单模态框
function showEditBlacklistModal(id, encodedWords) {
    const words = decodeURIComponent(encodedWords);
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.tabIndex = -1;
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open'); // 锁定滚动条
    overlay.onclick = () => { document.body.classList.remove('modal-open'); overlay.remove(); modal.remove(); };
    modal.innerHTML = `
        <h3>编辑黑名单</h3>
        <label>黑名单词（每行一个）: <textarea id="edit-blacklist-words" rows="4">${words.split(',').join('\n')}</textarea></label>
        <div class="modal-buttons">
            <button class="modal-btn modal-cancel" onclick="document.body.classList.remove('modal-open'); document.querySelector('.modal-overlay').remove(); this.parentNode.parentNode.remove()">取消</button>
            <button class="modal-btn modal-save" onclick="updateBlacklistWords()">保存</button>
        </div>
    `;
    document.body.appendChild(modal);
    document.getElementById('edit-blacklist-words').focus();
    modal.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.body.classList.remove('modal-open');
            overlay.remove();
            modal.remove();
        }
    });
}

function editBookmark(id) {
    const url = document.getElementById('edit-url').value.trim();
    const title = document.getElementById('edit-title').value.trim();
    const dateInput = document.getElementById('edit-date').value;
    // 转换为 MySQL 兼容的 YYYY-MM-DD HH:MM:SS 格式
    const date = dateInput ? dateInput.replace('T', ' ') + ':00' : formatDate(new Date());
    const isBookmarked = document.getElementById('edit-isBookmarked').checked;

    fetchWithRetry(`api.php?api_token=${encodeURIComponent(api_token)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'edit', id, url, title, date, isBookmarked })
    })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                showMessage(data.message);
                fetchData();
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('请求失败: ' + error, true));
}

function confirmDelete(id) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open'); // 锁定

    const confirmDiv = document.createElement('div');
    confirmDiv.className = 'confirm-container';
    confirmDiv.tabIndex = -1; // 添加 tabIndex 使确认窗口可聚焦
    confirmDiv.innerHTML = `
        <p>确定软删除此记录吗？</p>
        <div class="confirm-buttons">
            <button class="confirm-btn confirm-no" onclick="document.body.classList.remove('modal-open'); document.querySelector('.modal-overlay').remove(); this.parentNode.parentNode.remove()">取消</button>
            <button class="confirm-btn confirm-yes" onclick="deleteBookmark(${id}); document.body.classList.remove('modal-open'); document.querySelector('.modal-overlay').remove(); this.parentNode.parentNode.remove()">删除</button>
        </div>
    `;
    document.body.appendChild(confirmDiv);
    confirmDiv.focus(); // 设置焦点到确认窗口
    overlay.onclick = () => { document.body.classList.remove('modal-open'); overlay.remove(); confirmDiv.remove(); };
    confirmDiv.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.body.classList.remove('modal-open');
            overlay.remove(); // 核心修复：清理中间层
            confirmDiv.remove();
        }
    });
}

function deleteBookmark(id) {
    fetchWithRetry(`api.php?api_token=${encodeURIComponent(api_token)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id })
    })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                showMessage(data.message);
                fetchData();
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('请求失败: ' + error, true));
}

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.select-row');
    checkboxes.forEach(checkbox => {
        checkbox.checked = elements.selectAll.checked;
    });
}

function confirmBatchDelete() {
    const selectedIds = Array.from(document.querySelectorAll('.select-row:checked')).map(cb => parseInt(cb.dataset.id));
    if (selectedIds.length === 0) {
        showMessage('请至少选择一条记录进行删除', true);
        return;
    }

    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open'); // 锁定

    const confirmDiv = document.createElement('div');
    confirmDiv.className = 'confirm-container';
    confirmDiv.tabIndex = -1; // 添加 tabIndex 使确认窗口可聚焦
    confirmDiv.innerHTML = `
        <p>确定软删除选中的 ${selectedIds.length} 条记录吗？</p>
        <div class="confirm-buttons">
            <button class="confirm-btn confirm-no" onclick="document.body.classList.remove('modal-open'); document.querySelector('.modal-overlay').remove(); this.parentNode.parentNode.remove()">取消</button>
            <button class="confirm-btn confirm-yes" onclick="batchDelete([${selectedIds.join(',')}]); document.body.classList.remove('modal-open'); document.querySelector('.modal-overlay').remove(); this.parentNode.parentNode.remove()">删除</button>
        </div>
    `;
    document.body.appendChild(confirmDiv);
    confirmDiv.focus(); // 设置焦点到确认窗口
    overlay.onclick = () => { document.body.classList.remove('modal-open'); overlay.remove(); confirmDiv.remove(); };
    confirmDiv.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.body.classList.remove('modal-open');
            overlay.remove(); // 核心修复：清理中间层
            confirmDiv.remove();
        }
    });
}

// 修改为使用 api.php 的批量删除接口
function batchDelete(ids) {
    fetchWithRetry(`api.php?api_token=${encodeURIComponent(api_token)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'batch_delete', ids })
    })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                showMessage(`成功删除 ${data.count} 条记录`);
                fetchData();
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('批量删除请求失败: ' + error, true));
}

function searchBookmarks() {
    const search = elements.search.value;
    const searchType = elements.searchType.value;
    const startDate = elements.startDate.value;
    const endDate = elements.endDate.value;
    const perPage = elements.perPage.value;
    window.history.pushState({}, '', `?page=1&per_page=${perPage}&search=${encodeURIComponent(search)}&search_type=${searchType}&start_date=${startDate}&end_date=${endDate}`);
    fetchData();
}

// 快速设置日期范围
function setQuickDate(type) {
    const today = new Date();
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    let start, end;
    end = formatDate(today);

    switch (type) {
        case 'today':
            start = end;
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);
            start = end = formatDate(yesterday);
            break;
        case 'before_yesterday':
            const beforeYesterday = new Date(today);
            beforeYesterday.setDate(today.getDate() - 2);
            start = end = formatDate(beforeYesterday);
            break;
        case 'this_week':
            const dayOfWeek = today.getDay(); // 0 is Sunday
            const monday = new Date(today);
            const diff = today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1); // adjust when day is sunday
            monday.setDate(diff);
            start = formatDate(monday);
            break;
    }

    elements.startDate.value = start;
    elements.endDate.value = end;
    searchBookmarks();
}

// 重置搜索
function resetSearch() {
    elements.search.value = ''; // 清空搜索框
    elements.startDate.value = '';
    elements.endDate.value = '';
    elements.searchType.value = 'keyword';
    elements.search.style.display = 'inline-block';
    elements.dateRangeInputs.style.display = 'none';

    const perPage = elements.perPage.value;
    window.history.pushState({}, '', `?page=1&per_page=${perPage}&search=&search_type=keyword&start_date=&end_date=`); // 重置 URL 参数
    fetchData(); // 刷新数据
}

function changePage(newPage) {
    const perPage = elements.perPage.value;
    const search = elements.search.value;
    const searchType = elements.searchType.value;
    const startDate = elements.startDate.value;
    const endDate = elements.endDate.value;
    window.history.pushState({}, '', `?page=${newPage}&per_page=${perPage}&search=${encodeURIComponent(search)}&search_type=${searchType}&start_date=${startDate}&end_date=${endDate}`);
    fetchData();
}

function updatePerPage(perPage) {
    const search = elements.search.value;
    const searchType = elements.searchType.value;
    const startDate = elements.startDate.value;
    const endDate = elements.endDate.value;
    window.history.pushState({}, '', `?page=1&per_page=${perPage}&search=${encodeURIComponent(search)}&search_type=${searchType}&start_date=${startDate}&end_date=${endDate}`);
    fetchData();
}

// 添加防抖功能以优化搜索性能
let debounceTimer;
function debounceSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(searchBookmarks, 2000); // 2000ms 延迟
}

// 通过 ID 触发编辑模态框 (解决字符串转义问题)
function editBookmarkById(id) {
    // 该函数由 renderTable 后续逻辑配合，或直接在内存中查找
    // 这里我们直接查询 DOM 获取数据，或通过全局缓存。由于当前代码没有全局缓存，我们从 tr 的 onclick 参数重构为数据属性读取
    const tr = document.querySelector(`tr[data-id="${id}"]`);
    if (!tr) return;

    // 我们在 renderTable 时已经把数据存入了内存或数据属性。
    // 为了最快修复，我们直接在 renderTable 时传递更安全的参数，或者在这里读取数据。
    // 方案：在 renderTable 中把 bookmark 对象存入全局缓存
    if (window.bookmarksCache && window.bookmarksCache[id]) {
        const b = window.bookmarksCache[id];
        showEditModal(b.id, b.url, b.title, b.date, b.isBookmarked);
    }
}

// 侧边栏折叠切换
function toggleSidebar(force = null) {
    const body = document.body;
    // 核心修正：如果传入 force，必须明确应用到 classList 上
    const isCollapsed = force !== null ? force : !body.classList.contains('sidebar-collapsed');

    // 如果是切换模式（force 为 null），toggle() 会返回处理后的状态
    if (force === null) {
        const newState = body.classList.toggle('sidebar-collapsed');
        // 确保同步
        if (newState) {
            document.querySelectorAll('.sidebar details[open]').forEach(el => el.removeAttribute('open'));
        }
        localStorage.setItem('sidebar_collapsed', newState ? 'true' : 'false');
    } else {
        // 如果是强制模式
        body.classList.toggle('sidebar-collapsed', force);
        if (force) {
            document.querySelectorAll('.sidebar details[open]').forEach(el => el.removeAttribute('open'));
        }
        localStorage.setItem('sidebar_collapsed', force ? 'true' : 'false');
    }
}

// 手机端侧边栏切换（抽屉模式）
function toggleMobileSidebar() {
    const body = document.body;
    body.classList.toggle('mobile-sidebar-open');

    // 如果是打开抽屉，强制展开侧边栏（移除折叠类），忽略之前的状态
    if (body.classList.contains('mobile-sidebar-open')) {
        body.classList.remove('sidebar-collapsed');
        // 手机端临时展开不应覆盖用户的全局记忆

        // 添加点击屏幕其他地方关闭的逻辑
        const handleOutsideClick = (e) => {
            const sidebar = document.getElementById('sidebar');
            const fab = document.getElementById('mobile-fab');

            // 如果点击的是侧边栏内部或悬浮球，不处理
            if (sidebar.contains(e.target) || fab.contains(e.target)) return;

            // 否则关闭侧边栏并移除监听
            body.classList.remove('mobile-sidebar-open');
            document.removeEventListener('click', handleOutsideClick);
        };

        // 延迟绑定监听，防止当前点击立即触发
        setTimeout(() => {
            document.addEventListener('click', handleOutsideClick);
        }, 10);
    }
}

// 点击背景切换逻辑
function handleSidebarClick(e) {
    const isCollapsed = document.body.classList.contains('sidebar-collapsed');

    // 如果侧边栏处于折叠状态，点击任何区域都应先展开
    if (isCollapsed) {
        toggleSidebar(false); // 强制展开
        // 注意：这里不 return，允许事件继续传导给子元素（如 details/summary），实现一键双开
    } else {
        // 在展开状态下：
        // 只有点击侧边栏背景、section 容器空白处，或特定的切换区域才执行折叠
        // 排除掉对 details 内部内容的点击
        if (e.target.id === 'sidebar' ||
            e.target.classList.contains('sidebar-section') ||
            e.target.closest('#sidebar-toggle') ||
            e.target.closest('.sidebar-header h1')) {
            toggleSidebar();
        }
    }
}

// 处理单元格点击选中复选框
function handleCheckboxCellClick(e, td) {
    if (e.target.tagName === 'INPUT') return;
    const cb = td.querySelector('input[type="checkbox"]');
    if (cb) {
        cb.checked = !cb.checked;
    }
}

// 切换深色/浅色模式
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
}

// 初始化函数，集中管理事件监听
function init() {
    // 计算滚动条宽度以防止页面抖动
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    document.documentElement.style.setProperty('--scrollbar-width', `${scrollbarWidth}px`);

    // 恢复主题设置 (优先 localStorage，其次系统偏好)
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.setAttribute('data-theme', 'dark');
    }

    // 恢复侧边栏状态
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
        // 如果初始是折叠的，自动收起 details (逻辑同步)
        document.querySelectorAll('.sidebar details').forEach(el => el.removeAttribute('open'));
    }

    // 同步 LocalStorage 的 api_token 到输入框
    const tokenInput = document.getElementById('api_token');
    if (api_token && tokenInput) {
        tokenInput.value = api_token;
    }

    // 同步 URL 中的 per_page 参数到 <select>
    const urlParams = new URLSearchParams(window.location.search);
    const perPageFromUrl = urlParams.get('per_page');
    const validOptions = [5, 10, 20, 30, 50, 100];
    if (perPageFromUrl && validOptions.includes(parseInt(perPageFromUrl))) {
        elements.perPage.value = perPageFromUrl;
    } else {
        elements.perPage.value = 50; // 默认 50
    }

    // 侧边栏点击监听
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.addEventListener('click', handleSidebarClick);
    }

    elements.search.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') searchBookmarks();
    });
    elements.search.addEventListener('input', debounceSearch);

    // 搜索类型切换逻辑
    elements.searchType.addEventListener('change', () => {
        const type = elements.searchType.value;
        if (type === 'keyword') {
            elements.search.style.display = 'inline-block';
            elements.dateRangeInputs.style.display = 'none';
        } else {
            elements.search.style.display = 'none';
            elements.dateRangeInputs.style.display = 'inline-block';
        }
    });

    // 新增书签回车监听
    [elements.addUrl, elements.addTitle, elements.addDate].forEach(el => {
        if (el) el.addEventListener('keypress', e => { if (e.key === 'Enter') addBookmark(); });
    });

    elements.perPage.addEventListener('change', () => updatePerPage(elements.perPage.value)); // 确保更改条数触发更新
    // 检查 api_token 是否存在
    if (!api_token) {
        showMessage('请先输入并保存 API Token', true);
    } else {
        // 初始化加载（并行执行提升速度）
        Promise.all([
            fetchData(),
            fetchBlacklist()
        ]).catch(err => {
            console.error('初始化加载失败:', err);
        });
    }
}

document.addEventListener('DOMContentLoaded', init);
