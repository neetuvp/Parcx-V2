

            <!-- date and time -->
            <div class="col-md-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="far fa-clock"></i></span>
                    </div>
                    <input type="text" class="form-control float-right" id="reservationtime" autocomplete="off"
                        placeholder="<?=$json["choose_datetime_range"]?>">
                </div>
            </div>

            <!-- search -->
            <div class="col-md-2">
                <button type="button" class="btn btn-block btn-secondary" id="view-report-button"><?=$json["view_report"]?></button>
            </div>

            <!-- loader -->
            <div class='col-1' id='loader'>
                <img src='../../../dist/img/loading.gif'>
            </div>

        </div>

        <div class="additional-menu-right">
            <div id="action-buttons">
                <div class="btn-group">
                    <button type="button" class="btn btn-warning" id="export"><?=$json["export"]?></button>
                    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">                       
                    </button>
                    <div class="dropdown-menu" role="menu">
                        <a class="dropdown-item" href="#" id="export_excel_report"><?=$json["export_to_excel"]?></a>
                        <a class="dropdown-item" href="#" id="export_pdf_report"><?=$json["export_to_pdf"]?></a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- end / additional menu -->

