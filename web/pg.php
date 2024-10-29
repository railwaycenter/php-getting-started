<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .section h3 {
            margin-top: 0;
            color: #333;
        }

        input[type="text"] {
            padding: 10px;
            margin-right: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: calc(100% - 22px);
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Modal styles */
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
            xhr.open('POST', 'pgsql.php', true);
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
                getRooms(); // Refresh room list
            });
        }

        function getRooms() {
            sendRequest('get', 'action=get', function(response) {
                const rooms = JSON.parse(response);
                const roomList = rooms.map(room => `<li>${room.room_id} - ${room.room_name}</li>`).join('');
                document.getElementById('room_list').innerHTML = `<ul>${roomList}</ul>`;
            });
        }

        function updateRoom() {
            const id = document.getElementById('update_id').value;
            const newRoomId = document.getElementById('new_room_id').value;
            const newRoomName = document.getElementById('new_room_name').value;
            const data = `action=update&id=${encodeURIComponent(id)}&new_room_id=${encodeURIComponent(newRoomId)}&new_room_name=${encodeURIComponent(newRoomName)}`;

            sendRequest('update', data, function(response) {
                showMessage(response);
                getRooms(); // Refresh room list
            });
        }

        function deleteRoom() {
            const id = document.getElementById('delete_id').value;
            const data = `action=delete&id=${encodeURIComponent(id)}`;

            sendRequest('delete', data, function(response) {
                showMessage(response);
                getRooms(); // Refresh room list
            });
        }

        function renameColumn() {
            const oldColumnName = document.getElementById('old_column_name').value;
            const newColumnName = document.getElementById('new_column_name').value;
            const data = `action=rename_column&old_column_name=${encodeURIComponent(oldColumnName)}&new_column_name=${encodeURIComponent(newColumnName)}`;

            sendRequest('rename_column', data, function(response) {
                showMessage(response);
            });
        }

        function showColumnNames() {
            sendRequest('get_columns', '', function(response) {
                const columns = JSON.parse(response);
                const columnList = columns.map(col => `<li>${col}</li>`).join('');
                document.getElementById('column_names').innerHTML = `<ul>${columnList}</ul>`;
            });
        }

        function deleteColumn() {
            const columnName = document.getElementById('column_name').value;
            const data = `action=drop_column&column_name=${encodeURIComponent(columnName)}`;

            sendRequest('drop_column', data, function(response) {
                showMessage(response);
            });
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Room Management</h1>

    <div class="section">
        <h3>Add Room</h3>
        Room ID: <input type="text" id="room_id" required>
        Room Name: <input type="text" id="room_name" required>
        <button onclick="addRoom()">Add Room</button>
    </div>

    <div class="section">
        <h3>Room List</h3>
        <button onclick="getRooms()">Refresh Room List</button>
        <div id="room_list"></div>
    </div>

    <div class="section">
        <h3>Update Room</h3>
        Room ID to Update: <input type="text" id="update_id" required>
        New Room ID: <input type="text" id="new_room_id" required>
        New Room Name: <input type="text" id="new_room_name" required>
        <button onclick="updateRoom()">Update Room</button>
    </div>

    <div class="section">
        <h3>Delete Room</h3>
        Room ID: <input type="text" id="delete_id" required>
        <button onclick="deleteRoom()">Delete Room</button>
    </div>

    <div class="section">
        <h3>Rename Column</h3>
        Old Column Name: <input type="text" id="old_column_name" required>
        New Column Name: <input type="text" id="new_column_name" required>
        <button onclick="renameColumn()">Rename Column</button>
    </div>

    <div class="section">
        <h3>Show Column Names</h3>
        <button onclick="showColumnNames()">Get Column Names</button>
        <div id="column_names"></div>
    </div>

    <div class="section">
        <h3>Delete Column</h3>
        Column Name: <input type="text" id="column_name" required>
        <button onclick="deleteColumn()">Delete Column</button>
    </div>
</div>

<!-- Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeMessage()">&times;</span>
        <p id="modal-message"></p>
    </div>
</div>
</body>
</html>
