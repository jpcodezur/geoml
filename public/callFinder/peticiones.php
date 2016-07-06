<?php

include("includes/funciones.php");

switch ($_POST['action']) {
    case "getCampaigns":
        $source = $_POST['source'];
        if ($source == "cloud") {
            $link = DBConnectAmazon();
        } else {
            $link = DBConnect();
        }
        $sql = "select vc.campaign_id, vc.campaign_name
                                                    from vicidial_campaigns vc
                                                    order by campaign_name";
        $resBD = mysql_query($sql, $link);

        $data = array();
        while ($rowBD = mysql_fetch_array($resBD)) {
            $data[] = $rowBD;
        }
        echo json_encode($data);
        DBDisconnect($link);
        
        break;

    case "getCampaignsByType":
        if ($_POST['type'] == "INBOUND") {
            $type = "where vc.campaign_allow_inbound = 'Y'";
        } else if ($_POST['type'] == "OUTBOUND") {
            $type = "where vc.campaign_allow_inbound = 'N'";
        } else {
            $type = "";
        }

        //Hacer consulta
        $data = array();
        //$link = DBConnect();
        $source = $_POST['source'];
        if ($source == "cloud") {
            $link = DBConnectAmazon();
        } else {
            $link = DBConnect();
        }

        $sql = "select vc.campaign_id, vc.campaign_name
                from vicidial_campaigns vc
                $type";
        $resBD = mysql_query($sql, $link);
        while ($rowBD = mysql_fetch_array($resBD)) {
            $data[] = $rowBD;
        }

        //Mostrar datos en JSON
        echo json_encode($data);

        DBDisconnect($link);

        break;

    case "getDisposByCampaign":
        $campaign_id = $_POST['campaign_id'];
        $source = $_POST['source'];
        $data = array();

        if ($source == "cloud") {
            $link = DBConnectAmazon();
        } else {
            $link = DBConnect();
        }

        //Hacer consulta
        $sql = "select status, status_name
                from vicidial_campaign_statuses vcs
                where vcs.campaign_id = '$campaign_id'
                union all
                select status, status_name
                from vicidial_statuses vs
                order by status_name";
        $resBD = mysql_query($sql, $link);
        while ($rowBD = mysql_fetch_array($resBD)) {
            $data[] = $rowBD;
        }

        //Mostrar datos en JSON
        echo json_encode($data);

        DBDisconnect($link);

        break;

    case "getIngroupsByCampaign":
        $campaign_id = $_POST['campaign_id'];
        $data = array();
        //$link = DBConnect();
        $source = $_POST['source'];
        if ($source == "cloud") {
            $link = DBConnectAmazon();
        } else {
            $link = DBConnect();
        }

        //Hacer consulta
        $sql = "select distinct vig.group_id, vig.group_name
                from vicidial_campaigns vc, vicidial_inbound_groups vig
                where vc.closer_campaigns like concat('%', vig.group_id, '%')
                and vc.campaign_id = '$campaign_id'";
        $resBD = mysql_query($sql, $link);
        while ($rowBD = mysql_fetch_array($resBD)) {
            $data[] = $rowBD;
        }

        //Mostrar datos en JSON
        echo json_encode($data);

        DBDisconnect($link);

        break;

    case "getAgentsByCampaign":
        $campaign_id = $_POST['campaign_id'];
        $data = array();

        //$link = DBConnect();
        $source = $_POST['source'];
        if ($source == "cloud") {
            $link = DBConnectAmazon();
        } else {
            $link = DBConnect();
        }

        //Si la campania = ALL, mostrar todos
        $userFilterSQL = "and (vug.allowed_campaigns like '%$campaign_id%' or vug.allowed_campaigns like '%ALL-CAMPAIGNS%')";
        if ($campaign_id == "ALL") {
            $userFilterSQL = "";
        }

        //Hacer consulta
        $sql = "select vu.user, vu.full_name
                from vicidial_users vu, vicidial_user_groups vug
                where vu.user_group = vug.user_group
                $userFilterSQL
                order by full_name";
        $resBD = mysql_query($sql, $link);
        while ($rowBD = mysql_fetch_array($resBD)) {
            $data[] = $rowBD;
        }

        //Mostrar datos en JSON
        echo json_encode($data);

        DBDisconnect($link);
        break;

    case "getRecordings":
        
        $source = $_POST['source'];
        if ($source == "cloud") {
            $link = DBConnectAmazon();
        } else {
            $link = DBConnect();
        }
        
        $data = array();

        //Campos adicionales
        !empty($_POST['custom_fields']) ? $custom_fields = split(";", $_POST['custom_fields']) : $custom_fields = "";
        $fields = Array("COMMENTS" => "ifnull(vls.comments, 'N/A') as Comments", "FULL_NAME" => "ifnull(concat(vls.first_name, ' ', vls.last_name), 'N/A') as 'Customer Name'", "ADDRESS1" => "ifnull(vls.address1, 'N/A') as 'Address 1'", "ADDRESS2" => "ifnull(vls.address2, 'N/A') as 'Address 2'",
            "ADDRESS3" => "ifnull(vls.address3, 'N/A') as 'Address 3'", "CITY" => "ifnull(vls.city, 'N/A') as City", "STATE" => "ifnull(vls.state, '') as 'State'", "POSTAL_CODE" => "ifnull(vls.postal_code, 'N/A') as 'Postal Code'",
            "GENDER" => "ifnull(vls.gender, 'N/A') as Gender", "DATE_OF_BIRTH" => "ifnull(vls.date_of_birth, 'N/A') as 'Date of Birth'", "EMAIL" => "ifnull(vls.email, 'N/A') as 'E-Mail'");

        //Filtros
        !empty($_POST['agents']) ? $agents = split(";", $_POST['agents']) : $agents = "";
        $campaign_id = $_POST['campaign'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        !empty($_POST['dispos']) ? $dispos = split(";", $_POST['dispos']) : $dispos = "";
        $hour_from = $_POST['hour_from'];
        $hour_to = $_POST['hour_to'];
        !empty($_POST['ingroups']) ? $ingroups = split(";", $_POST['ingroups']) : $ingroups = "";
        $length_from = $_POST['length_from'];
        $length_to = $_POST['length_to'];
        $type = $_POST['type'];
        $phone_number = ereg_replace("[^0-9]", "", $_POST['phone_number']); //Corregir phone_number
        $source = $_POST['source'];

        //Otras variables
        /*$order_by = "";
        $order_by = $_POST['order_by'];*/
        $page = $_POST['page'];

        //Armar condiciones multiples
        $agentsSQL = sqlMultipleCondition($agents, "vl.user", true);
        $disposSQL = sqlMultipleCondition($dispos, "vl.status", true);
        $ingroupsSQL = sqlMultipleCondition($ingroups, "vl.campaign_id", true);
        //Armar condiciones simples
        !empty($date_from) ? $dateFromSQL = " and vl.call_date >= '$date_from $hour_from'" : $dateFromSQL = "";
        !empty($date_to) ? $dateToSQL = " and vl.call_date <= '$date_to $hour_to'" : $dateToSQL = "";
        !empty($length_from) ? $lengthFromSQL = " and rl.length_in_sec >= $length_from" : $lengthFromSQL = "";
        !empty($length_to) ? $lengthToSQL = " and rl.length_in_sec <= $length_to" : $lengthToSQL = "";
        !empty($phone_number) ? $phoneNumberSQL = " and vl.phone_number like '%$phone_number%'" : $phoneNumberSQL = "";
        //Inbound / Outbound campaign_id
        if ($campaign_id != 'ALL') {
            $campaignIDSQLOutbound = sqlSimpleCondition($campaign_id, "vl.campaign_id", true);
            $campaignIDSQLInbound = " and campaign_id in(select distinct vig.group_id from vicidial_campaigns vc, vicidial_inbound_groups vig
                                    where vc.closer_campaigns like concat('%', vig.group_id, '%') and vc.campaign_id = '$campaign_id')";
        } else {
            $campaignIDSQLOutbound = "";
            $campaignIDSQLInbound = "";
        }

        //Armar campos extra
        $customFieldsSQL = "";
        if (!empty($custom_fields)) {
            for ($x = 0; $x < count($custom_fields); $x++) {
                $customFieldsSQL .= ", " . $fields[$custom_fields[$x]];
            }
        }

        //Orden de registros
        !empty($order_by) ? $orderBySQL = "order by `$order_by`" : $orderBySQL = "order by `Call Date`"; //Ordenar por fecha ascendente por defecto
        //Conectar a la base
        if ($source == "local") {
            $link = DBConnect();
        } elseif ($source == "cloud") {
            $link = DBConnectAmazon();
        }

        //TODO: Mejorar performance (vistas, etc)
        //Armar SQL
        $sqlSelectInboundDispos = "ifnull((select vs.status_name from vicidial_statuses vs where vs.status = vl.status
                                    UNION
                                    select vcs.status_name from vicidial_campaign_statuses vcs where vcs.status = vl.status
                                    and vcs.campaign_id in(select vc.campaign_id from vicidial_campaigns vc where vc.closer_campaigns like concat('%',vl.campaign_id ,'%'))
                                    limit 0,1
                                    ), 'N/A') as Disposition";
        $sqlSelectOutboundDispos = "ifnull((select vs.status_name from vicidial_statuses vs where vs.status = vl.status
                                    UNION
                                    select vcs.status_name from vicidial_campaign_statuses vcs where vcs.status = vl.status
                                    and vcs.campaign_id = vl.campaign_id
                                    limit 0,1
                                    ), 'N/A') as Disposition";

        $sqlCalcFoundRowsInbound = "";
        $sqlCalcFoundRowsOutbound = "";
        switch ($type) {
            case "INBOUND":
                $sqlCalcFoundRowsInbound = "SQL_CALC_FOUND_ROWS";
                break;
            case "OUTBOUND":
                $sqlCalcFoundRowsOutbound = "SQL_CALC_FOUND_ROWS";
                break;
            default:
                $sqlCalcFoundRowsInbound = "SQL_CALC_FOUND_ROWS";
                break;
        }

        $sqlSelectInbound = "(select distinct $sqlCalcFoundRowsInbound rl.vicidial_id as 'ID', rl.filename, vu.full_name as 'Agent', vl.phone_number as 'Phone Number', '$source' as db_source, vig.group_name as 'Campaign ID / Queue', vl.call_date as 'Call Date', SEC_TO_TIME(rl.length_in_sec) as Length, vl.term_reason as 'Term', $sqlSelectInboundDispos$customFieldsSQL";
        $sqlSelectOutbound = "(select distinct $sqlCalcFoundRowsOutbound rl.vicidial_id as 'ID', rl.filename, vu.full_name as 'Agent', vl.phone_number as 'Phone Number', '$source' as db_source, vc.campaign_name as 'Campaign ID / Queue', vl.call_date as 'Call Date', SEC_TO_TIME(rl.length_in_sec) as Length, vl.term_reason as 'Term', $sqlSelectOutboundDispos$customFieldsSQL";

        $sqlInbound = "$sqlSelectInbound
                       from recording_log rl, vicidial_users vu, vicidial_list vls, vicidial_closer_log vl, vicidial_inbound_groups vig
                       where vl.closecallid = rl.vicidial_id
                       and vig.group_id = vl.campaign_id
                       and vl.user = vu.user
                       and vls.lead_id = vl.lead_id
                       and rl.length_in_sec is not null
                       $agentsSQL
                       $disposSQL
                       $ingroupsSQL
                       $dateFromSQL
                       $dateToSQL
                       $lengthFromSQL
                       $lengthToSQL
                       $campaignIDSQLInbound
                       $phoneNumberSQL)";

        $sqlOutbound = "$sqlSelectOutbound
                        from recording_log rl, vicidial_users vu, vicidial_list vls, vicidial_log vl, vicidial_campaigns vc
                        where vl.uniqueid = rl.vicidial_id
                        and vc.campaign_id = vl.campaign_id
                        and vl.user = vu.user
                        and rl.length_in_sec is not null
                        and vls.lead_id = vl.lead_id
                        $agentsSQL
                        $disposSQL
                        $campaignIDSQLOutbound
                        $dateFromSQL
                        $dateToSQL
                        $lengthFromSQL
                        $lengthToSQL
                        $phoneNumberSQL)";

        $sqlGlobal = "$orderBySQL
                      limit " . (($page - 1) * REG_PAG_COUNT) . "," . REG_PAG_COUNT;

        //Verificar si inbound o outbound
        switch ($type) {
            case "OUTBOUND":
                $sql = "$sqlOutbound
                        $sqlGlobal";
                break;
            case "INBOUND":
                $sql = "$sqlInbound
                        $sqlGlobal";
                break;
            default: //ALL
                $sql = "$sqlInbound
                UNION ALL
                $sqlOutbound
                $sqlGlobal";
                break;
        }

        //die($sql);
        //mail("egonzalez@telecomnetworks.net", "Sql","$sql");
		
		
        $resBD = mysql_query($sql, $link);
        while ($rowBD = mysql_fetch_array($resBD, MYSQL_ASSOC)) {
            $data[] = $rowBD;
        }

        if (empty($data)) {
            $error = array("error_code" => "ERROR-1", "error_description" => "No recordings found.");
            echo json_encode($error);
        } else {
            $sql = "SELECT FOUND_ROWS() AS num_results_total";
            $rowBD = mysql_fetch_array(mysql_query($sql, $link));
            $cantidadRegistros = "" . $rowBD['num_results_total'] . "";

            $aditionalData = array($cantidadRegistros);
            $enviar = array_merge($data, $aditionalData);
            echo json_encode($enviar);
        }

        break;

    default:
        break;
}
?>