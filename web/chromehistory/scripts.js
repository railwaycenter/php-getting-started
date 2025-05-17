// 从 index.php 中提取的 JavaScript 逻辑，添加初始化函数和优化
// 版本号 v1.0.17

// 缓存 DOM 引用以提升性能
const elements = {
    message: document.getElementById('message'),
    search: document.getElementById('search'),
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

// 设置默认日期为当前本地时间，格式化为 datetime-local 所需的字符串
function formatLocalDateTime(date)
{
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// 检查并设置 addDate 的默认值
if(elements.addDate)
{
    elements.addDate.value = formatLocalDateTime(new Date()); // 设置新增书签的默认时间为本地时间
} else
{
    console.error('Element with id "add-date" not found');
}

function showMessage(message, isError = false, callback = null)
{
    const modal = document.createElement('div');
    modal.className = `modal ${isError ? 'error' : 'success'}`; // 使用 modal 类并根据类型添加 error 或 success
    modal.tabIndex = -1; // 添加 tabIndex 使模态窗口可聚焦
    modal.innerHTML = `
        <p>${message}</p>
        <div class="modal-buttons">
            <button class="modal-btn modal-cancel" onclick="this.parentNode.parentNode.remove()">关闭</button>
        </div>
    `;
    document.body.appendChild(modal);
    modal.focus(); // 设置焦点到模态窗口
    modal.addEventListener('keydown', (e) =>
    {
        if(e.key === 'Escape')
        {
            modal.remove();
            if(callback) callback();
        }
    });

    // 3 秒后自动关闭
    setTimeout(() =>
    {
        modal.remove();
        if(callback) callback();
    }, 3000);
}

function formatDate(timestamp)
{
    const date = new Date(parseInt(timestamp));
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // 月份从 0 开始
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`; // 格式为 YYYY-MM-DD HH:MM:SS
}

function renderTable(data)
{
    elements.bookmarkTableBody.innerHTML = '';
    data.bookmarks.forEach(bookmark =>
    {
        const tr = document.createElement('tr');
        tr.dataset.id = bookmark.id;
        tr.innerHTML = `
            <td><input type="checkbox" class="select-row" data-id="${bookmark.id}"></td>
            <td>${bookmark.id}</td>
            <td class="text-ellipsis" title="${bookmark.url}"><a href="${bookmark.url}" target="_blank">${bookmark.url}</a></td>
            <td class="text-ellipsis" title="${bookmark.title}">${bookmark.title}</td>
            <td class="date-column" title="${bookmark.date}">${bookmark.date}</td>
            <td>${bookmark.isBookmarked === true ? '是' : '否'}</td>
            <td class="created-at-column" title="${bookmark.created_at}">${bookmark.created_at}</td>
            <td>
                <button class="action-btn edit-btn" onclick="showEditModal(${bookmark.id}, '${encodeURIComponent(bookmark.url)}', '${encodeURIComponent(bookmark.title)}', ${bookmark.date}, ${bookmark.isBookmarked})">修改</button>
                <button class="action-btn delete-btn" onclick="confirmDelete(${bookmark.id})">删除</button>
            </td>
        `;
        elements.bookmarkTableBody.appendChild(tr);
    });
    // 更新 <select> 的选中值以反映实际条数
    elements.perPage.value = data.itemsPerPage;
    renderPagination(data);
}

// 渲染黑名单列表（单条记录）
function renderBlacklistTable(data)
{
    elements.blacklistTableBody.innerHTML = '';
    const tr = document.createElement('tr');
    tr.dataset.id = data.id;
    tr.innerHTML = `
        <td>${data.id}</td>
        <td>${data.words}</td>
        <td>
            <button class="action-btn edit-btn" onclick="showEditBlacklistModal(${data.id}, '${encodeURIComponent(data.words)}')">修改</button>
        </td>
    `;
    elements.blacklistTableBody.appendChild(tr);
}

function renderPagination(data)
{
    // 渲染分页控件，包括每页条数选择、页码导航和自定义跳转
    const pagination = document.querySelector('.pagination');
    const {totalPages, currentPage, itemsPerPage} = data;

    // 每页条数选择，默认值为 30，选项包括 5, 10, 20, 30, 50, 100
    let html = '<span>每页显示: </span><select id="per-page" onchange="updatePerPage(this.value)">';
    [5, 10, 20, 30, 50, 100].forEach(option =>
    {
        html += `<option value="${option}" ${itemsPerPage == option ? 'selected' : ''}>${option}</option>`;
    });
    html += '</select>';

    if(totalPages > 1)
    {
        // 添加“上一页”按钮
        if(currentPage > 1)
        {
            html += `<a href="#" onclick="changePage(${currentPage - 1}); return false;">上一页</a>`;
        }

        // 计算分页范围，前后各显示 3 页
        const range = 3;
        const start = Math.max(1, currentPage - range);
        const end = Math.min(totalPages, currentPage + range);

        // 如果起始页大于 1，显示第 1 页和省略号
        if(start > 1)
        {
            html += `<a href="#" onclick="changePage(1); return false;">1</a>`;
            if(start > 2) html += '<span>...</span>';
        }

        // 渲染页码按钮
        for(let i = start; i <= end; i++)
        {
            html += `<a href="#" onclick="changePage(${i}); return false;" ${i == currentPage ? 'class="current"' : ''}>${i}</a>`;
        }

        // 如果结束页小于总页数，显示省略号和最后一页
        if(end < totalPages)
        {
            if(end < totalPages - 1) html += '<span>...</span>';
            html += `<a href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a>`;
        }

        // 添加“下一页”按钮
        if(currentPage < totalPages)
        {
            html += `<a href="#" onclick="changePage(${currentPage + 1}); return false;">下一页</a>`;
        }

        // 添加自定义跳转输入框和按钮
        html += `
            <span>跳转到: </span>
            <input type="number" id="jump-page" min="1" max="${totalPages}" value="${currentPage}" style="width: 50px; padding: 5px;">
            <button onclick="changePage(document.getElementById('jump-page').value)">跳转</button>
        `;
    }

    pagination.innerHTML = html;
}

function fetchData()
{
    // 移除 showMessage('加载中...', false) 以避免信息覆盖
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page') || 1;
    const perPage = urlParams.get('per_page') || 30; // 从 URL 获取 per_page，默认 30
    const search = urlParams.get('search') || '';

    fetch(`api.php?api_token=${api_token}&page=${page}&per_page=${perPage}&search=${encodeURIComponent(search)}`)
        .then(response => response.json().then(data => ({ok: response.ok, data})))
        .then(({ok, data}) =>
        {
            if(ok)
            {
                renderTable(data);
            } else
            {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('数据加载失败: ' + error, true));
}

// 获取黑名单
function fetchBlacklist()
{
    fetch(`api.php?api_token=${api_token}&action=get_blacklist`)
        .then(response => response.json().then(data => ({ok: response.ok, data})))
        .then(({ok, data}) =>
        {
            if(ok)
            {
                renderBlacklistTable(data);
            } else
            {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('获取黑名单失败: ' + error, true));
}

function addBookmark()
{
    const url = elements.addUrl.value.trim();
    const title = elements.addTitle.value.trim();
    const dateInput = elements.addDate ? elements.addDate.value : null; // 检查 addDate 是否存在
    const date = dateInput ? Math.round(new Date(dateInput).getTime()) : Math.round(Date.now());
    const isBookmarked = elements.addIsBookmarked.checked;

    if(!url || !title)
    {
        showMessage('URL 和标题不能为空', true);
        return;
    }
    if(!url.match(/^https?:\/\/.+/))
    {
        showMessage('请输入有效的 URL', true);
        return;
    }

    fetch(`api.php?api_token=${api_token}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'add', url, title, date, isBookmarked})
    })
        .then(response => response.json().then(data => ({ok: response.ok, data})))
        .then(({ok, data}) =>
        {
            if(ok)
            {
                showMessage(data.message);
                fetchData();
            } else
            {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('请求失败: ' + error, true));
}

// 更新黑名单词
function updateBlacklistWords()
{
    const confirmDiv = document.createElement('div');
    confirmDiv.className = 'confirm-container';
    confirmDiv.tabIndex = -1;
    confirmDiv.innerHTML = `
        <p>确定更新黑名单吗？此操作将覆盖现有黑名单。</p>
        <div class="confirm-buttons">
            <button class="confirm-btn confirm-yes" onclick="confirmUpdateBlacklistWords(); this.parentNode.parentNode.remove()">是</button>
            <button class="confirm-btn confirm-no" onclick="this.parentNode.parentNode.remove()">否</button>
        </div>
    `;
    document.body.appendChild(confirmDiv);
    confirmDiv.focus();
    confirmDiv.addEventListener('keydown', (e) =>
    {
        if(e.key === 'Escape')
        {
            confirmDiv.remove();
        }
    });
}

function confirmUpdateBlacklistWords()
{
    const modal = document.querySelector('.modal');
    const wordsInput = modal ? document.getElementById('edit-blacklist-words').value.trim() : '';
    const words = wordsInput.split('\n').map(w => w.trim()).filter(w => w).join(','); // 每行转为逗号分隔
    fetch(`api.php?api_token=${api_token}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update_blacklist', id: 1, words})
    })
        .then(response => response.json().then(data => ({ok: response.ok, data})))
        .then(({ok, data}) =>
        {
            if(ok)
            {
                showMessage(data.message);
                fetchBlacklist();
                const editModal = document.querySelector('.modal');
                if(editModal) editModal.remove(); // 保存后关闭编辑模态框
            } else
            {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('更新黑名单失败: ' + error, true));
}

function showEditModal(id, encodedUrl, encodedTitle, date, isBookmarked)
{
    const url = decodeURIComponent(encodedUrl);
    const title = decodeURIComponent(encodedTitle);
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.tabIndex = -1; // 添加 tabIndex 使模态窗口可聚焦
    modal.innerHTML = `
        <h3>编辑书签</h3>
        <label>URL: <input type="text" id="edit-url" value="${url}" required></label>
        <label>标题: <input type="text" id="edit-title" value="${title}" required></label>
        <label>日期: <input type="datetime-local" id="edit-date" value="${formatLocalDateTime(date)}" required></label>
        <label><input type="checkbox" id="edit-isBookmarked" ${isBookmarked ? 'checked' : ''}> 是否书签</label>
        <div class="modal-buttons">
            <button class="modal-btn modal-save" onclick="editBookmark(${id}); this.parentNode.parentNode.remove()">保存</button>
            <button class="modal-btn modal-cancel" onclick="this.parentNode.parentNode.remove()">取消</button>
        </div>
    `;
    document.body.appendChild(modal);
    document.getElementById('edit-url').focus(); // 将焦点设置为 edit-url 输入框
    modal.addEventListener('keydown', (e) =>
    {
        if(e.key === 'Escape')
        {
            modal.remove();
        }
    });
}

// 编辑黑名单模态框
function showEditBlacklistModal(id, encodedWords)
{
    const words = decodeURIComponent(encodedWords);
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.tabIndex = -1;
    modal.innerHTML = `
        <h3>编辑黑名单</h3>
        <label>黑名单词（每行一个）: <textarea id="edit-blacklist-words" rows="4">${words.split(',').join('\n')}</textarea></label>
        <div class="modal-buttons">
            <button class="modal-btn modal-save" onclick="updateBlacklistWords()">保存</button>
            <button class="modal-btn modal-cancel" onclick="this.parentNode.parentNode.remove()">取消</button>
        </div>
    `;
    document.body.appendChild(modal);
    document.getElementById('edit-blacklist-words').focus();
    modal.addEventListener('keydown', (e) =>
    {
        if(e.key === 'Escape')
        {
            modal.remove();
        }
    });
}

function editBookmark(id)
{
    const url = document.getElementById('edit-url').value.trim();
    const title = document.getElementById('edit-title').value.trim();
    const dateInput = document.getElementById('edit-date').value;
    const date = dateInput ? Math.round(new Date(dateInput).getTime()) : Math.round(Date.now());
    const isBookmarked = document.getElementById('edit-isBookmarked').checked;

    fetch(`api.php?api_token=${api_token}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'edit', id, url, title, date, isBookmarked})
    })
        .then(response => response.json().then(data => ({ok: response.ok, data})))
        .then(({ok, data}) =>
        {
            if(ok)
            {
                showMessage(data.message);
                fetchData();
            } else
            {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('请求失败: ' + error, true));
}

function confirmDelete(id)
{
    const confirmDiv = document.createElement('div');
    confirmDiv.className = 'confirm-container';
    confirmDiv.tabIndex = -1; // 添加 tabIndex 使确认窗口可聚焦
    confirmDiv.innerHTML = `
        <p>确定软删除此记录吗？</p>
        <div class="confirm-buttons">
            <button class="confirm-btn confirm-yes" onclick="deleteBookmark(${id}); this.parentNode.parentNode.remove()">是</button>
            <button class="confirm-btn confirm-no" onclick="this.parentNode.parentNode.remove()">否</button>
        </div>
    `;
    document.body.appendChild(confirmDiv);
    confirmDiv.focus(); // 设置焦点到确认窗口
    confirmDiv.addEventListener('keydown', (e) =>
    {
        if(e.key === 'Escape')
        {
            confirmDiv.remove();
        }
    });
}

function deleteBookmark(id)
{
    fetch(`api.php?api_token=${api_token}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', id})
    })
        .then(response => response.json().then(data => ({ok: response.ok, data})))
        .then(({ok, data}) =>
        {
            if(ok)
            {
                showMessage(data.message);
                fetchData();
            } else
            {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('请求失败: ' + error, true));
}

function toggleSelectAll()
{
    const checkboxes = document.querySelectorAll('.select-row');
    checkboxes.forEach(checkbox =>
    {
        checkbox.checked = elements.selectAll.checked;
    });
}

function confirmBatchDelete()
{
    const selectedIds = Array.from(document.querySelectorAll('.select-row:checked')).map(cb => parseInt(cb.dataset.id));
    if(selectedIds.length === 0)
    {
        showMessage('请至少选择一条记录进行删除', true);
        return;
    }

    const confirmDiv = document.createElement('div');
    confirmDiv.className = 'confirm-container';
    confirmDiv.tabIndex = -1; // 添加 tabIndex 使确认窗口可聚焦
    confirmDiv.innerHTML = `
        <p>确定软删除选中的 ${selectedIds.length} 条记录吗？</p>
        <div class="confirm-buttons">
            <button class="confirm-btn confirm-yes" onclick="batchDelete(${JSON.stringify(selectedIds)}); this.parentNode.parentNode.remove()">是</button>
            <button class="confirm-btn confirm-no" onclick="this.parentNode.parentNode.remove()">否</button>
        </div>
    `;
    document.body.appendChild(confirmDiv);
    confirmDiv.focus(); // 设置焦点到确认窗口
    confirmDiv.addEventListener('keydown', (e) =>
    {
        if(e.key === 'Escape')
        {
            confirmDiv.remove();
        }
    });
}

// 修改为使用 api.php 的批量删除接口
function batchDelete(ids)
{
    fetch(`api.php?api_token=${api_token}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'batch_delete', ids})
    })
        .then(response => response.json().then(data => ({ok: response.ok, data})))
        .then(({ok, data}) =>
        {
            if(ok)
            {
                showMessage(`成功删除 ${data.count} 条记录`);
                fetchData();
            } else
            {
                showMessage(data.message, true);
            }
        })
        .catch(error => showMessage('批量删除请求失败: ' + error, true));
}

function searchBookmarks()
{
    const search = elements.search.value;
    const perPage = elements.perPage.value;
    window.history.pushState({}, '', `?page=1&per_page=${perPage}&search=${encodeURIComponent(search)}`);
    fetchData();
}

// 重置搜索
function resetSearch()
{
    elements.search.value = ''; // 清空搜索框
    const perPage = elements.perPage.value;
    window.history.pushState({}, '', `?page=1&per_page=${perPage}&search=`); // 重置 URL 参数
    fetchData(); // 刷新数据
}

function changePage(newPage)
{
    const perPage = elements.perPage.value;
    window.history.pushState({}, '', `?page=${newPage}&per_page=${perPage}&search=${encodeURIComponent(elements.search.value)}`);
    fetchData();
}

function updatePerPage(perPage)
{
    const search = elements.search.value;
    window.history.pushState({}, '', `?page=1&per_page=${perPage}&search=${encodeURIComponent(search)}`);
    fetchData();
}

// 添加防抖功能以优化搜索性能
let debounceTimer;
function debounceSearch()
{
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(searchBookmarks, 2000); // 2000ms 延迟
}

// 初始化函数，集中管理事件监听
function init()
{
    // 同步 LocalStorage 的 api_token 到输入框
    const tokenInput = document.getElementById('api_token');
    if (api_token && tokenInput) {
        tokenInput.value = api_token;
    }
    
    // 同步 URL 中的 per_page 参数到 <select>
    const urlParams = new URLSearchParams(window.location.search);
    const perPageFromUrl = urlParams.get('per_page');
    const validOptions = [5, 10, 20, 30, 50, 100];
    if(perPageFromUrl && validOptions.includes(parseInt(perPageFromUrl)))
    {
        elements.perPage.value = perPageFromUrl;
    } else
    {
        elements.perPage.value = 30; // 默认 30
    }

    elements.search.addEventListener('keypress', (e) =>
    {
        if(e.key === 'Enter') searchBookmarks();
    });
    elements.search.addEventListener('input', debounceSearch);
    elements.perPage.addEventListener('change', () => updatePerPage(elements.perPage.value)); // 确保更改条数触发更新
// 检查 api_token 是否存在
    if (!api_token) {
        showMessage('请先输入并保存 API Token', true);
    } else {
        fetchData(); // 初始加载书签数据
        fetchBlacklist(); // 初始加载黑名单数据
    }
}

document.addEventListener('DOMContentLoaded', init);