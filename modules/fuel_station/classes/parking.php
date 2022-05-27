<?php

define("DB_HOST", "DBServer");
define("DB_USER", "parcxservice");
define("DB_PASSWORD", "1fromParcx!19514");
define("DB_NAME", "parcx_server");
define("DB_NAME_REPORTING", "parcx_reporting");
define("DB_NAME_DASHBOARD", "parcx_dashboard");

class parking {

    function db_connect($db) {
        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, $db);
        if (!$con)
            echo "Connection attempt failed";
        else
            return $con;
    }
    
    
    function get_interfaces()
        {
        $con = $this->db_connect(DB_NAME);
        if ($con != NULL) {
            $sql = "SELECT interface_name from interface_settings where interface_type='api'";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
            while ($data = mysqli_fetch_assoc($result)) {
                echo "<option>".$data['interface_name']."</option>";
            }
            mysqli_close($con);    
        }
        }
    
    function get_pump_status()
        {
        $con = $this->db_connect(DB_NAME_DASHBOARD);
        if ($con != NULL) {
            $sql = "SELECT pump_name,a.status,date_time FROM fueling_station_sensor_status a,parcx_server.fueling_station_pump_settings b where a.controller_id=b.controller_id and a.pin_number=b.monitor_pin order by pump_number";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
           
            $html_data = "";
            $html_data .= '<table class="table table-bordered jspdf-table">';
            // table header
            $html_data .= '<thead>';
            $html_data .= '<tr>';
            $html_data .= '<th>Pump name</th>';
            $html_data .= '<th>Status</th>';            
            $html_data .= '<th>Last update</th>';            
            $html_data .= '</tr>';
            $html_data .= '</thead>';

            // table data
            $html_data .= '<tbody>';
            while ($data = mysqli_fetch_assoc($result)) {
                $html_data .= '<tr>';
                $html_data .= '<td>' . $data['pump_name'] . '</td>';
                if($data['status']==1)
                    $html_data .= '<td><span class="fa-stack fa-1x" ><i class="fa fa-circle fa-stack-2x text-success" title="Vehicle Present"></i></span></td>';
                else
                    $html_data .= '<td><span class="fa-stack fa-1x"><i class="fa fa-circle fa-stack-2x text-danger" title="Vehicle Absent"></i></span></td>';
                
                //$html_data .= '<td>' . $data['status'] . '</td>';
                $html_data .= '<td>' . $data['date_time'] . '</td>';               
                $html_data .= '</tr>';
            }
            $html_data .= '</tbody>';
            $html_data .= '</table>';
            echo $html_data;
            mysqli_close($con);    
            }
        }

    function show_pump_details() {
        $con = $this->db_connect(DB_NAME);
        if ($con != NULL) {
            $camera = array();
            $camera[0] = "Camera";
            $sql = "select device_number,device_name from system_devices where device_category=8 order by device_number";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
            while ($row = mysqli_fetch_assoc($result)) {
                $camera[$row["device_number"]] = $row["device_name"];
            }

            $sql = "select * from fueling_station_pump_settings order by pump_number";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
            while ($row = mysqli_fetch_assoc($result)) {
                $html = '';

                $html .= '<div class="card  card-dark mb-2 pump-dashboard">';
                $html .= '<div class="card-header p-2">';
                $html .= '<h3 class="card-title" id="pump_' . $row["pump_number"] . '_heading">' . $row["pump_name"] . '</h3>';
                $html .= '<div class="card-tools">';
                $html .= '<button type="button" class="btn btn-dark btn-sm" data-card-widget="collapse" title="Collapse">';
                $html .= '<i class="fas fa-minus"></i>';
                $html .= '</button>';
                $html .= '</div>';
                $html .= '</div>';

                $html .= '<div class="card-body">';
                $html .= '<div class="row ">';
                $html .= '<div class="col">';
                $html .= '<h5 id="camera-name1-' . $row["pump_number"] . '">' . $camera[$row["camera_1"]] . '</h5>';
                $html .= '<div class="row" >';
                $html .= '<img id="plate-image1-' . $row["pump_number"] . '" src ="" width="100%"; height="350";>';
                $html .= '</div>';
                $html .= '<div class="row">';
                $html .= '<div class="col">';
                $html .= '<ul class="nav flex-column">';
                $html .= '<li class="nav-item">';
                $html .= '<span class="nav-link">Date time';
                $html .= '<span class="float-right txt-trsn " id="response-date1-' . $row["pump_number"] . '"></span>';
                $html .= '</span>';
                $html .= '</li>';
                $html .= '<li class="nav-item">';
                $html .= '<span class="nav-link">Plate Number';
                $html .= '<span class="float-right txt-trsn " id="plate-number1-' . $row["pump_number"] . '"></span>';
                $html .= '</span>';
                $html .= '</li>';

                $html .= '<li class="nav-item">';
                $html .= '<span class="nav-link">Confidence Rate';
                $html .= '<span class="float-right txt-trsn " id="confidence-rate1-' . $row["pump_number"] . '"></span>';
                $html .= '</span>';
                $html .= '</li>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="ml-2 col"  >';
                $html .= '<h5 id="camera-name-2-' . $row["pump_number"] . '">' . $camera[$row["camera_2"]] . '</h5>';
                $html .= '<div class="row" >';
                $html .= '<img id="plate-image2-' . $row["pump_number"] . '" src ="" width="100%"; height="350";>';
                $html .= '</div>';
                $html .= '<div class="row" >';
                $html .= '<div class="col"  >';
                $html .= '<ul class="nav flex-column">';
                $html .= '<li class="nav-item">';
                $html .= '<span class="nav-link">Date time';
                $html .= '<span class="float-right txt-trsn " id="response-date2-' . $row["pump_number"] . '"></span>';
                $html .= '</span>';
                $html .= '</li>';

                $html .= '<li class="nav-item">';
                $html .= '<span class="nav-link">Plate Number';
                $html .= '<span class="float-right txt-trsn " id="plate-number2-' . $row["pump_number"] . '"></span>';
                $html .= '</span>';
                $html .= '</li>';

                $html .= '<li class="nav-item">';
                $html .= '<span class="nav-link">Confidence Rate';
                $html .= '<span class="float-right txt-trsn " id="confidence-rate2-' . $row["pump_number"] . '"></span>';
                $html .= '</span>';
                $html .= '</li>';


                $html .= '</div>';
                $html .= '</div>';

                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';

                $html .= '</div>';

                echo $html;
            }
            mysqli_close($con);
        }
    }

    function get_pump_details() {
        $con = $this->db_connect(DB_NAME_REPORTING);
        if ($con != NULL) {
            $sql = "SELECT fueling_station_transaction_report.* from fueling_station_transaction_report,(select pump_id,max(id) as id from fueling_station_transaction_report group by pump_id) last_transaction where fueling_station_transaction_report.id=last_transaction.id order by pump_id";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
            $i = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $resultrow["pump_id"] = $row["pump_id"];
                $resultrow["transaction_date_time"] = $row["date_time"];
                $resultrow["transaction_id"] = $row["id"];
                $resultrow["plate_captured_id1"] = $row["plate_captured_id1"];
                $resultrow["plate_captured_id2"] = $row["plate_captured_id2"];
                $resultrow["comment"] = $row["comment"];
                if ($resultrow["plate_captured_id1"] == 0) {
                    $resultrow["plate_number1"] = "";
                    $resultrow["plate_image_name1"] = "";
                    $resultrow["capture_date_time1"] = "";
                    $resultrow["plate_country1"] = "";
                    $resultrow["confidence_rate1"] = "";
                    $resultrow["image_location1"] = "";
                }
                if ($resultrow["plate_captured_id2"] == 0) {
                    $resultrow["plate_number2"] = "";
                    $resultrow["plate_image_name2"] = "";
                    $resultrow["capture_date_time2"] = "";
                    $resultrow["plate_country2"] = "";
                    $resultrow["confidence_rate2"] = "";
                    $resultrow["image_location2"] = "";
                }

                $sql = "SELECT * from plates_captured where id in(" . $resultrow["plate_captured_id1"] . "," . $resultrow["plate_captured_id2"] . ")";
                $plates_capturedresult = mysqli_query($con, $sql) or die(mysqli_error($con));
                while ($plates_captured = mysqli_fetch_assoc($plates_capturedresult)) {
                    if ($plates_captured["id"] == $resultrow["plate_captured_id1"]) {
                        $resultrow["camera1"] = $plates_captured["camera_name"];
                        $resultrow["plate_number1"] = $plates_captured["plate_number"];
                        $resultrow["plate_image_name1"] = $plates_captured["plate_image_name"];
                        $resultrow["capture_date_time1"] = $plates_captured["capture_date_time"];
                        $resultrow["plate_country1"] = $plates_captured["plate_country"];
                        $resultrow["confidence_rate1"] = $plates_captured["confidence_rate"];
                        $plates_captured["plate_image_name"] = str_replace(' ', '%20', $plates_captured["plate_image_name"]);
                        $plates_captured["plate_image_name"] = str_replace('#', '%23', $plates_captured["plate_image_name"]);
                        $resultrow["image_location1"] = "http://" . $_SERVER['SERVER_NAME'] . "/ANPR/Images/" . $plates_captured["camera_device_number"] . "/" . substr($plates_captured["capture_date_time"], 0, 10) . "/Scene_" . $plates_captured["plate_image_name"];
                    } else {
                        $resultrow["camera2"] = $plates_captured["camera_name"];
                        $resultrow["plate_number2"] = $plates_captured["plate_number"];
                        $resultrow["plate_image_name2"] = $plates_captured["plate_image_name"];
                        $resultrow["capture_date_time2"] = $plates_captured["capture_date_time"];
                        $resultrow["plate_country2"] = $plates_captured["plate_country"];
                        $resultrow["confidence_rate2"] = $plates_captured["confidence_rate"];
                        $plates_captured["plate_image_name"] = str_replace(' ', '%20', $plates_captured["plate_image_name"]);
                        $plates_captured["plate_image_name"] = str_replace('#', '%23', $plates_captured["plate_image_name"]);
                        $resultrow["image_location2"] = "http://" . $_SERVER['SERVER_NAME'] . "/ANPR/Images/" . $plates_captured["camera_device_number"] . "/" . substr($plates_captured["capture_date_time"], 0, 10) . "/Scene_" . $plates_captured["plate_image_name"];
                    }
                }

                $response[$i] = $resultrow;
                $i++;
            }

            mysqli_close($con);
            echo (json_encode($response));
        }
    }

    function get_daily_report($data) {
        $report_date = $data["report_date"];
        $con = $this->db_connect(DB_NAME_REPORTING);
        $sql = "SELECT * FROM `plates_captured` where  date(capture_date_time)='" . $report_date . "' and plate_number!='###' ";
        $plate_result = mysqli_query($con, $sql) or die(mysqli_error($con));
        $sql = "SELECT * FROM `fueling_station_transaction_report` where date(date_time)='" . $report_date . "'";
        $transaction_result = mysqli_query($con, $sql) or die(mysqli_error($con));
        $sql = "SELECT * FROM `fueling_station_sensor_status_report` where pin_number in(4,5)  and status=1 and date(date_time)='" . $report_date . "'";
        $sensor_result = mysqli_query($con, $sql) or die(mysqli_error($con));

        $plate_number = "";
        $total_plates = 0;
        $total_transactions = 0;
        $total_pump_active_missed_transaction = 0;
        $total_pump_not_active_missed_transaction = 0;
        $total_vehicle_passing = 0;
        $valid_transaction = false;
        $missed_transaction = false;
        $pump_active = false;
        $row_number = -1;
        $plate_details = array();
        $i = 0;
        $pump_id = 0;

        while ($plates = mysqli_fetch_assoc($plate_result)) {
            $row_number++;
            if ($plates["plate_number"] != $plate_number) {

                $plate_number = $plates["plate_number"];
                $id = $plates["id"];
                $total_plates++;
                $valid_transaction = false;
                $capture_date_time = new DateTime($plates["capture_date_time"]);
                $details["plate_number"] = $plate_number;
                $details["date_time"] = $plates["capture_date_time"];

                if ($total_transactions > 0)
                    mysqli_data_seek($transaction_result, $total_transactions - 1);
                else
                    mysqli_data_seek($transaction_result, 0);

                while ($transactions = mysqli_fetch_assoc($transaction_result)) {
                    if ($transactions["plate_number1"] == $plate_number || $transactions["plate_number2"] == $plate_number) {
                        $pump_id = $transactions["pump_id"];
                        $total_transactions++;
                        $valid_transaction = true;
                        $details["result"] = "Valid transaction exist in Pump " . $transactions["pump_id"];
                        //echo $plate_number." Valid transaction Pump: ".$transactions["pump_id"]." : ".$transactions["date_time"]."<br>";
                        break;
                    }
                    $transaction_time = new DateTime($transactions["date_time"]);
                    $diffInSeconds = $transaction_time->getTimestamp() - $capture_date_time->getTimestamp();
                    if ($diffInSeconds > 180)
                        break;
                }

                if ($valid_transaction == false) {
                    if ($total_transactions > 0)
                        mysqli_data_seek($sensor_result, $total_transactions - 1);
                    else
                        mysqli_data_seek($sensor_result, 0);
                    $missed_transaction = false;
                    $pump_active = false;
                    $next_plate_capture_time = "";

                    while ($plates = mysqli_fetch_assoc($plate_result)) {
                        if ($plates["plate_number"] != $plate_number) {
                            $next_plate_capture_time = $plates["capture_date_time"];
                            break;
                        }
                    }

                    while ($sensor = mysqli_fetch_assoc($sensor_result)) {
                        if ($sensor["date_time"] >= $next_plate_capture_time)
                            break;

                        $sensor_time = new DateTime($sensor["date_time"]);
                        $diffInSeconds = $sensor_time->getTimestamp() - $capture_date_time->getTimestamp();
                        if ($diffInSeconds >= -10 && $diffInSeconds <= 10) {
                            $pump_active = true;
                            if ($sensor["duration"] > "00:00:03") {
                                $total_pump_active_missed_transaction++;
                                $missed_transaction = true;
                                //echo $id . " : " . $plate_number . " Missed transaction sensor active " . $sensor["id"] . " : " . $sensor["date_time"] . "<br>";
                                $details["result"] = "Pump active .Missed transaction";
                                break;
                            }
                        }
                    }

                    if ($missed_transaction == false) {
                        if ($pump_active == true) {
                            $total_vehicle_passing++;
                            //echo $id." : ".$plate_number." Vehicle passing<br>";
                            $details["result"] = "Vehicle passing .Missed transaction";
                        } else {
                            $total_pump_not_active_missed_transaction++;
                            //echo $id . " : " . $plate_number . " Pump not active<br>";
                            $details["result"] = "Pump not active.Missed transaction";
                        }
                    }

                    mysqli_data_seek($plate_result, $row_number);
                }
                $plate_details[$i] = $details;
                $i++;
            }
        }
        mysqli_close($con);

        $n = $i;
        $html = "<table class='table table-bordered col-md-10' id='RecordsTable'><thead><th>Date time</th><th>Plate</th><th>Result</th></thead><tbody>";
        for ($i = 0; $i < $n; $i++) {
            $html .= "<tr><td>" . $plate_details[$i]["date_time"] . "</td><td>" . $plate_details[$i]["plate_number"] . "</td><td>" . $plate_details[$i]["result"] . "</td></tr>";
        }
        $html .= "</tbody></table>";
        echo "<div class='card'><div class='card-body'>";
        if ($total_plates > 0) {
            echo "<h4>Total plates: " . $total_plates . "</h4>";
            echo "<h4>Total valid transactions: " . $total_transactions . "</h4>";
            echo "<h4>Total vehicle passing: " . $total_vehicle_passing . "</h4>";

            echo "<h4>Total pump active Missed transactions: " . $total_pump_active_missed_transaction . "</h4>";
            echo "<h4>Total pump not active Missed transactions: " . $total_pump_not_active_missed_transaction . "</h4>";

            $accuracy = ($total_transactions + $total_vehicle_passing) / $total_plates;
            $accuracy = $accuracy * 100;
            echo "<h4>Accuracy: " . round($accuracy, 2) . "%</h4>";
            echo "<br><br>";
        }

        echo $html;
        echo "</div></div>";
    }

    function get_daily_report_old($data) {
        $from = $data["from"];
        $to = $data["to"];
        $pump1["Plates Match"] = 0;
        $pump2["Plates Match"] = 0;
        $pump1["Camera1 and camera2 not captured"] = 0;
        $pump2["Camera1 and camera2 not captured"] = 0;
        $pump1["Camera 1 not captured"] = 0;
        $pump2["Camera 1 not captured"] = 0;
        $pump1["Camera 2 not captured"] = 0;
        $pump2["Camera 2 not captured"] = 0;
        $pump1["Plate mismatch"] = 0;
        $pump2["Plate mismatch"] = 0;

        $con = $this->db_connect(DB_NAME_REPORTING);
        if ($con != NULL) {
            echo "<div class='card p-3 col-5'>";
            $total = 0;

            $html = "<table class='table table-bordered'><thead><th>Camera</th><th>Count</th></thead><tbody>";
            $sql = "select count(id) as count,camera_device_number from plates_captured where capture_date_time between '" . $from . "' and '" . $to . "' group by camera_device_number";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
            while ($data = mysqli_fetch_assoc($result)) {
                $total = $total + $data["count"];
                $html .= "<tr><td>Camera " . $data["camera_device_number"] . "</td><td>" . $data["count"] . "</td></tr>";
            }
            $html .= "</tbody></table>";

            echo "<h5>Total Plate Captured:" . $total . "</h5>";
            echo $html;
//            $sql= "select count(distinct plate_number) as count from plates_captured where capture_date_time between '".$from."' and '".$to."' and plate_number!='###'";         
//            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
//            if($data = mysqli_fetch_assoc($result))
//                echo "<h5>Total Valid Plate Captured:".$data["count"]."</h5><hr>";   

            $sql = "select count(id) as count,comment,pump_id from fueling_station_transaction_report where date_time between '" . $from . "' and '" . $to . "' group by comment,pump_id  ORDER by comment desc";
//            echo $sql."<br>";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
            $total = 0;


            while ($data = mysqli_fetch_assoc($result)) {
                $comment = $data["comment"];
                $pump = $data["pump_id"];
                if ($pump == 1)
                    $pump1[$comment] = $data["count"];
                else
                    $pump2[$comment] = $data["count"];
                $total = $total + $data["count"];
            }
            $html = "<table class='table table-bordered'><thead><th>Comment</th><th>Pump 1</th><th>Pump3</th></thead><tbody>";
            $html .= "<tr><td>Plates Match</td><td>" . $pump1["Plates Match"] . "</td><td>" . $pump2["Plates Match"] . "</td></tr>";
            $html .= "<tr><td>Plate mismatch</td><td>" . $pump1["Plate mismatch"] . "</td><td>" . $pump2["Plate mismatch"] . "</td></tr>";
            $html .= "<tr><td>Camera 1 not captured</td><td>" . $pump1["Camera 1 not captured"] . "</td><td>" . $pump2["Camera 1 not captured"] . "</td></tr>";
            $html .= "<tr><td>Camera 2 not captured</td><td>" . $pump1["Camera 2 not captured"] . "</td><td>" . $pump2["Camera 2 not captured"] . "</td></tr>";
            $html .= "<tr><td>Camera1 and camera2 not captured</td><td>" . $pump1["Camera1 and camera2 not captured"] . "</td><td>" . $pump2["Camera1 and camera2 not captured"] . "</td></tr>";

            $html .= "</tbody></table>";
            echo "<h5>Total transactions: " . $total . "</h5><br>";
            echo $html;

            if ($total > 0) {
                $sql = "select count(id) as count from interface_access_request where request_date_time between '" . $from . "' and '" . $to . "' and transaction_type='Vehicle Found'";
                $result = mysqli_query($con, $sql) or die(mysqli_error($con));
                $totalRequest = 0;
                if ($data = mysqli_fetch_assoc($result)) {
                    echo "<h5>Total request sent: " . $data["count"] . "</h5><br>";
                    $totalRequest = $data["count"];
                }

                $accuracy = ($totalRequest / $total) * 100;
                echo "<hr><h5>Accuracy: " . round($accuracy, 2) . " %</h5>";
            }
            echo "</div>";
            mysqli_close($con);
        }
    }

    function transaction_report($json) {
        $con = $this->db_connect(DB_NAME_REPORTING);
        if ($con != NULL) {
            $sql = "SELECT * from fueling_station_transaction_report where date_time between '" . $json["from"] . "' and '" . $json["to"] . "'";
            if (strlen($json["pump_id"]) > 0)
                $sql .= " and pump_id in(" . $json["pump_id"] . ")";
            if (strlen($json["plate_number"]) > 0)
                $sql .= " and (plate_number1 like '%" . $json["plate_number"] . "%' OR plate_number2 like '%" . $json["plate_number"] . "%')";
            $sql .= " order by id desc";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
            if (mysqli_num_rows($result) > 0) {
                $html_data = "";
                $html_data .= '<div class="row mb-4 jspdf-graph">';
                $html_data .= '<div class="col-lg-3 col-6">';
                $html_data .= '<div class="small-box bg-success">';
                $html_data .= '<div class="inner">';
                $html_data .= '<h3>' . mysqli_num_rows($result) . '</h3>';
                $html_data .= '<h6>Transactions</h6>';
                $html_data .= '</div>';
                $html_data .= '</div>';
                $html_data .= '</div>';
                $html_data .= '</div>';


                $html_data .= '<div class="card">';
                $html_data .= '<div class="card-body">';

                echo $html_data;
                $this->print_transaction_report_table($result);
                echo "</div></div>";
                }
          else 
              echo "<div class='card p-3'>No transactions found</div>";
          
            mysqli_close($con);
        }
    }

    function print_transaction_report_table($result) {
        if (mysqli_num_rows($result) > 0) {
            $html_data = "";
            $html_data .= '<table id="RecordsTable" class="table table-bordered jspdf-table">';
            // table header
            $html_data .= '<thead>';
            $html_data .= '<tr>';
            $html_data .= '<th>DATE TIME</th>';
            $html_data .= '<th>PUMP ID</th>';
            $html_data .= '<th>CAMERA 1 PLATE</th>';
            $html_data .= '<th>CAMERA 2 PLATE</th>';
            $html_data .= '<th>COMMENT</th>';
            $html_data .= '</tr>';
            $html_data .= '</thead>';

            // table data
            $html_data .= '<tbody>';
            while ($data = mysqli_fetch_assoc($result)) {
                $html_data .= '<tr data-toggle="modal" data-target="#transaction-detail" data-id="' . $data['id'] . '">';
                $html_data .= '<td>' . $data['date_time'] . '</td>';
                $html_data .= '<td>' . $data['pump_id'] . '</td>';
                $html_data .= '<td>' . $data['plate_number1'] . '</td>';
                $html_data .= '<td>' . $data['plate_number2'] . '</td>';
                $html_data .= '<td>' . $data['comment'] . '</td>';
                $html_data .= '</tr>';
            }
            $html_data .= '</tbody>';
            $html_data .= '</table>';
            echo $html_data;
        }        
    }

    function transaction_report_live() {
        $con = $this->db_connect(DB_NAME_REPORTING);
        if ($con != NULL) {
            $sql = "select * from fueling_station_transaction_report order by id desc limit 100";
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
            $this->print_transaction_report_table($result);
            mysqli_close($con);
        } else
            echo "No server connection";
    }

    function get_transaction_details($data) {
        $con = $this->db_connect(DB_NAME_REPORTING);
        if ($con != NULL) {
            $sql = "select * from fueling_station_transaction_report where id=" . $data["id"];

            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
            $i = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                $resultrow["pump_id"] = $row["pump_id"];
                $resultrow["transaction_date_time"] = $row["date_time"];
                $resultrow["transaction_id"] = $row["id"];
                $resultrow["plate_captured_id1"] = $row["plate_captured_id1"];
                $resultrow["plate_captured_id2"] = $row["plate_captured_id2"];
                $resultrow["comment"] = $row["comment"];

                if ($resultrow["plate_captured_id1"] == 0) {
                    if ($data["id"] != "") {
                        $sql = "select device_name from parcx_server.system_devices a,parcx_server.fueling_station_pump_settings b where a.camera_type=1 and a.pump_id=b.id and  b.pump_number=" . $resultrow["pump_id"];
                        $result = mysqli_query($con, $sql) or die(mysqli_error($con));
                        $row = mysqli_fetch_assoc($result);
                        $resultrow["camera1"] = $row["device_name"];
                    } else
                        $resultrow["camera1"] = "";
                    
                    $resultrow["plate_number1"] = "";
                    $resultrow["plate_image_name1"] = "";
                    $resultrow["capture_date_time1"] = "";
                    $resultrow["plate_country1"] = "";
                    $resultrow["confidence_rate1"] = "";
                    $resultrow["image_location1"] = "";
                }
                if ($resultrow["plate_captured_id2"] == 0) {
                    if ($data["id"] != "") {
                        $sql = "select device_name from parcx_server.system_devices a,parcx_server.fueling_station_pump_settings b where a.camera_type=2 and a.pump_id=b.id and  b.pump_number=" . $resultrow["pump_id"];
                        $result = mysqli_query($con, $sql) or die(mysqli_error($con));
                        $row = mysqli_fetch_assoc($result);
                        $resultrow["camera2"] = $row["device_name"];
                    } else
                        $resultrow["camera2"] = "";
                    
                    $resultrow["plate_number2"] = "";
                    $resultrow["plate_image_name2"] = "";
                    $resultrow["capture_date_time2"] = "";
                    $resultrow["plate_country2"] = "";
                    $resultrow["confidence_rate2"] = "";
                    $resultrow["image_location2"] = "";
                }

                $sql = "SELECT * from plates_captured where id in(" . $resultrow["plate_captured_id1"] . "," . $resultrow["plate_captured_id2"] . ")";

                $plates_capturedresult = mysqli_query($con, $sql) or die(mysqli_error($con));
                while ($plates_captured = mysqli_fetch_assoc($plates_capturedresult)) {
                    if ($plates_captured["id"] == $resultrow["plate_captured_id1"]) {
                        $resultrow["camera1"] = $plates_captured["camera_name"];
                        $resultrow["plate_number1"] = $plates_captured["plate_number"];
                        $resultrow["plate_image_name1"] = $plates_captured["plate_image_name"];
                        $resultrow["capture_date_time1"] = $plates_captured["capture_date_time"];
                        $resultrow["plate_country1"] = $plates_captured["plate_country"];
                        $resultrow["confidence_rate1"] = $plates_captured["confidence_rate"];
                        $plates_captured["plate_image_name"] = str_replace(' ', '%20', $plates_captured["plate_image_name"]);
                        $plates_captured["plate_image_name"] = str_replace('#', '%23', $plates_captured["plate_image_name"]);
                        $resultrow["image_location1"] = "http://" . $_SERVER['SERVER_NAME'] . "/ANPR/Images/" . $plates_captured["camera_device_number"] . "/" . substr($plates_captured["capture_date_time"], 0, 10) . "/Scene_" . $plates_captured["plate_image_name"];
                    } else {
                        $resultrow["camera2"] = $plates_captured["camera_name"];
                        $resultrow["plate_number2"] = $plates_captured["plate_number"];
                        $resultrow["plate_image_name2"] = $plates_captured["plate_image_name"];
                        $resultrow["capture_date_time2"] = $plates_captured["capture_date_time"];
                        $resultrow["plate_country2"] = $plates_captured["plate_country"];
                        $resultrow["confidence_rate2"] = $plates_captured["confidence_rate"];
                        $plates_captured["plate_image_name"] = str_replace(' ', '%20', $plates_captured["plate_image_name"]);
                        $plates_captured["plate_image_name"] = str_replace('#', '%23', $plates_captured["plate_image_name"]);
                        $resultrow["image_location2"] = "http://" . $_SERVER['SERVER_NAME'] . "/ANPR/Images/" . $plates_captured["camera_device_number"] . "/" . substr($plates_captured["capture_date_time"], 0, 10) . "/Scene_" . $plates_captured["plate_image_name"];
                    }
                }

                $html_data = "";
                $response = $resultrow;
                $i = 0;
                $sql = "SELECT * from interface_access_request where transaction_id=" . $resultrow["transaction_id"];
                $result = mysqli_query($con, $sql) or die(mysqli_error($con));

                if (mysqli_num_rows($result) > 0) {


                    while ($data = mysqli_fetch_assoc($result)) {

                        $html_data .= "<h5>" . $data['interface_name'] . "</h5>";
                        $html_data .= '<div class="row mt-2">';
                        $html_data .= '<div class="col-3">Url</div>';
                        $html_data .= '<div class="col">' . $data['url'] . '</div>';
                        $html_data .= '</div>';
                        $html_data .= '<div class="row">';
                        $html_data .= '<div class="col-3">Request date time</div>';
                        $html_data .= '<div class="col">' . $data['request_date_time'] . '</div>';
                        $html_data .= '</div>';
                        $html_data .= '<div class="row">';
                        $html_data .= '<div class="col-3">Response date time</div>';
                        $html_data .= '<div class="col">' . $data['response_date_time'] . '</div>';
                        $html_data .= '</div>';
                        $html_data .= '<div class="row">';
                        $html_data .= '<div class="col-3">Request</div>';
                        $html_data .= '<div class="col">' . $data['request'] . '</div>';
                        $html_data .= '</div>';
                        $html_data .= '<div class="row">';
                        $html_data .= '<div class="col-3">Response</div>';
                        $html_data .= '<div class="col">' . $data['response'] . '</div>';
                        $html_data .= '</div>';
                        $html_data .= '<div class="row">';
                        $html_data .= '<div class="col-3">Response code</div>';
                        $html_data .= '<div class="col">' . $data['response_code'] . '</div>';
                        $html_data .= '</div><hr>';
                    }
                }
                $response["api_transactions"] = $html_data;
            }

            echo (json_encode($response));
        }
        mysqli_close($con);
    }

    function get_access_request_report($json) {
        $con = $this->db_connect(DB_NAME_REPORTING);
        if ($con != NULL) {
            $sql = "SELECT * FROM interface_access_request LEFT Join  fueling_station_transaction_report on interface_access_request.transaction_id=fueling_station_transaction_report.id where request_date_time between '" . $json["from"] . "' and '" . $json["to"] . "'";
            if (strlen($json["pump_id"]) > 0)
                $sql .= " and pump_id in(" . $json["pump_id"] . ")";
            if (strlen($json["plate_number"]) > 0)
                $sql .= " and (plate_number1 like '%" . $json["plate_number"] . "%' OR plate_number2 like '%" . $json["plate_number"] . "%')";
            if (strlen($json["transaction_type"]) > 0)
                $sql .= " and interface_name='" . $json["transaction_type"] . "'";
            if (strlen($json["response_type"]) > 0) {
                if ($json["response_type"] == "success")
                    $sql .= " and interface_name='Adnoc-Vehicle-Found' and response like '%Success%'";
                else
                    $sql .= " and interface_name='Adnoc-Vehicle-Found' and response not like '%Success%'";
            }
            $sql .= " order by interface_access_request.id desc";
            //echo $sql;
            $result = mysqli_query($con, $sql) or die(mysqli_error($con));
            $html_data = "";
            if (mysqli_num_rows($result) > 0) {
                $html_data .= '<div class="row mb-4 jspdf-graph">';
                $html_data .= '<div class="col-lg-3 col-6">';
                $html_data .= '<div class="small-box bg-success">';
                $html_data .= '<div class="inner">';
                $html_data .= '<h3>' . mysqli_num_rows($result) . '</h3>';
                $html_data .= '<h6>Transactions</h6>';
                $html_data .= '</div>';
                $html_data .= '</div>';
                $html_data .= '</div>';
                $html_data .= '</div>';


                $html_data .= '<div class="card">';
                $html_data .= '<div class="card-body">';

                $html_data .= '<table id="RecordsTable" class="table table-bordered jspdf-table">';
                // table header
                $html_data .= '<thead>';
                $html_data .= '<tr>';
                $html_data .= '<th>TRANSACTION TYPE</th>';
                $html_data .= '<th>PUMP ID</th>';
                $html_data .= '<th>CAMERA 1 PLATE</th>';
                $html_data .= '<th>CAMERA 2 PLATE</th>';
                $html_data .= '<th>REQUEST DATE TIME</th>';
                $html_data .= '<th style="width:20%">REQUEST</th>';
                $html_data .= '<th>RESPONSE DATE TIME</th>';
                $html_data .= '<th>RESPONSE</th>';
                $html_data .= '<th>RESPONSE CODE</th>';
                $html_data .= '</tr>';
                $html_data .= '</thead>';

                // table data
                $html_data .= '<tbody>';
                while ($data = mysqli_fetch_assoc($result)) {
                    $html_data .= '<tr">';
                    $html_data .= '<td>' . $data['interface_name'] . '</td>';
                    $html_data .= '<td>' . $data['pump_id'] . '</td>';
                    $html_data .= '<td>' . $data['plate_number1'] . '</td>';
                    $html_data .= '<td>' . $data['plate_number2'] . '</td>';
                    $html_data .= '<td>' . $data['request_date_time'] . '</td>';
                    $html_data .= '<td>' . $data['request'] . '</td>';
                    $html_data .= '<td>' . $data['response_date_time'] . '</td>';
                    $html_data .= '<td>' . $data['response'] . '</td>';
                    $html_data .= '<td>' . $data['response_code'] . '</td>';
                    $html_data .= '</tr>';
                }
                $html_data .= '</tbody>';
                $html_data .= '</table></div></div>';

                mysqli_close($con);
                echo $html_data;
            } else
                echo "<div class='card p-3'>No transactions found</div>";
        }
    }

}
?>

