<html>
    <head>
        <script src="https://code.jquery.com/jquery-3.4.0.js"></script>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style>
            img {
                border  : 1px solid black;
                padding : 8px;
                width   : 250px;
            }
            #file {
                width   : 107px;
            }
            .sql {
                font-weight: bold;
            }
            .table {
                border: 1px solid black;
            }
            td {
                padding: 3px 5px 3px 5px;
                white-space: nowrap;
            }
            .fieldname {
                border: 1px solid black;
                font-weight: bold;
            }
            .fieldvalue {
                border: 1px solid black;
            }
        </style>
    </head>
    <body>

        <form id="form" method="POST" enctype="multipart/form-data">
            <input id="file" type="file" accept=".sql" name="name"/>
            <input type="submit"/>
        </form>

        <?php
        error_reporting(22519);

        if (isset($_FILES['name'])) {
            $file = $_FILES['name']['name'];
            $file_tmp = $_FILES['name']['tmp_name'];
        }

        function show() {
            global $file;
            $sql = strtoupper(file_get_contents($file));
            echo "<div class='sql'>" . $sql . "</div><br/>\r\n";

            require_once('../settings.inc');
            $link = mysqli_connect($server, $username, $password, $database);
            $result = mysqli_query($link, $sql);

            echo "<table class='table'>\r\n";
            echo "<tr>\r\n";
            $i = 0;
            $fields = [];
            while ($i < mysqli_num_fields($result)) {
                $meta = mysqli_fetch_field($result);
                $fields[$i] = $meta->name;
                echo "<td class='fieldname'>" . $meta->orgname . "</td>";
                $i++;
            }
            echo "</tr>\r\n";

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>\r\n";
                foreach ($fields as $name) {
                    echo "<td class='fieldvalue'>" . $row[$name] . "</td>";
                }
                echo "</tr>\r\n";
            }

            echo "</table>\r\n";
            mysqli_close($link);
        }

        if (isset($_FILES['name'])) {
            show();
        }
        ?>

    </body>
</html>
