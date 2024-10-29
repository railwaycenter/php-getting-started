<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management</title>
    <style>
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
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 300px;
            text-align: center;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script>
        function sendRequest(action, data, callback) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'backend.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                    callback(xhr.responseText);
                }
            };
            xhr.send(data);
        }

        function showMessage(message) {
            const modal = document.getElementById("myModal");
            document.getElementById("modal-message").innerText = message;
            modal.style.display = "block";
        }

        function closeMessage() {
            const modal = document.getElementById("myModal");
            modal.style.display = "none";
        }

        function addRoom() {
            const roomId = document.getElementById('room_id').value;
            const roomName = document.getElementById('room_name').value;
            const data = `action=add&room_id=${encodeURIComponent(roomId)}&room_name=${encodeURIComponent(roomName)}`;

            sendRequest('add', data, function(response) {
                showMessage(response);
                getRooms(); // 刷新房间列表
            });
        }

        function getRooms() {
            sendRequest('get', 'action=get', function(response) {
                document.getElementById('room_list').innerHTML = response;
            });
        }

        function updateRoom() {
            const id = document.getElementById('update_id').value;
            const newRoomId = document.getElementById('new_room_id').value;
            const newRoomName = document.getElementById('new_room_name').value;
            const data = `action=update&id=${encodeURIComponent(id)}&new_room_id=${encodeURIComponent(newRoomId)}&new_room_name=${encodeURIComponent(newRoomName)}`;

            sendRequest('update', data, function(response) {
                showMessage(response);
                getRooms(); // 刷新房间列表
            });
        }

        function deleteRoom() {
            const id = document.getElementById('delete_id').value;
            const data = `action=delete&id=${encodeURIComponent(id)}`;

            sendRequest('delete', data, function(response) {
                showMessage(response);
                getRooms(); // 刷新房间列表
            });
        }
    </script>
</head>
<body>
<h1>Room Management System</h1>

<div>
    <h3>Add Room</h3>
    Room ID: <input type="text" id="room_id" required>
    Room Name: <input type="text" id="room_name" required>
    <button onclick="addRoom()">Add Room</button>
</div>

<div>
    <h3>Room List</h3>
    <button onclick="getRooms()">Get Rooms</button>
    <div id="room_list"></div>
</div>

<div>
    <h3>Update Room</h3>
    ID: <input type="text" id="update_id" required>
    New Room ID: <input type="text" id="new_room_id" required>
    New Room Name: <input type="text" id="new_room_name" required>
    <button onclick="updateRoom()">Update Room</button>
</div>

<div>
    <h3>Delete Room</h3>
    ID: <input type="text" id="delete_id" required>
    <button onclick="deleteRoom()">Delete Room</button>
</div>

<!-- 自定义消息模态框 -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeMessage()">&times;</span>
        <p id="modal-message"></p>
    </div>
</div>
</body>
</html>
