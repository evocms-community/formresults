<link type="text/css" rel="stylesheet" href="../assets/modules/formresults/libs/webix/css/compact.css">
<script type="text/javascript" src="../assets/modules/formresults/libs/webix/js/webix.js"></script>

<style>
    #results {
        border-bottom: 1px solid rgb(235,235,235);
        width: 100%;
        margin: calc(-1.25rem + 1px) 0 0.5rem;
    }

    .webix_message_area {
        display: none !important;
    }
</style>

<div class="tab-page" id="tab_main">
    <h2 class="tab">
        <?= $form['caption'] ?>
    </h2>

    <script type="text/javascript">
        tpFormResults.addTabPage(document.getElementById('tab_main'));
    </script>

    <div class="row">
        <div id="results"></div>
        <div style="clear:both"></div>
    </div>

    <div id="pager"></div>
</div>

<script>
    var removeResult = function(e, id) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        if (!confirm('<?= $_lang['formresults.confirm_delete'] ?>')) {
            return;
        }

        fetch('<?= $moduleUrl ?>&type=<?= $form['alias'] ?>&action=delete', {
            method: 'POST',
            body: new URLSearchParams({
                result_id: id
            })
        }).then(function(response) {
            window.location.reload();
        });
    };

    var removeResults = function(e) {
        e.preventDefault();

        if (!confirm('<?= $_lang['formresults.confirm_delete_all'] ?>')) {
            return;
        }

        fetch('<?= $moduleUrl ?>&type=<?= $form['alias'] ?>&action=deleteall', {
            method: 'POST'
        }).then(function(response) {
            window.location.reload();
        });
    };

    webix.ready(function(){
        webix.ui({
            responsive: true,
            container: "results",
            view: "datatable",
            moduleUrl: '<?= $moduleUrl ?>',
            //url: "<?= $moduleUrl ?>&ajax=results",
            data: <?= json_encode($results, JSON_UNESCAPED_UNICODE) ?>,
            columns: <?= json_encode($columns, JSON_UNESCAPED_UNICODE) ?>,
            autoheight: true,
            checkboxRefresh: true,
            fixedRowHeight: false,
            scrollX: false,
            rowLineHeight: 15,
            hover: 'hover',
            pager: {
                    //template: "{common.prev()} {common.pages()} {common.next()}",
                    size: 20,
                    container: "pager",
                    group: 10
                },
            resizeColumn: true,
            editable: false,
            borderless: true,
            navigation: true,
            math: true,
            on: {
                onAfterLoad: function() {
                    webix.delay(function() {
                        this.adjustRowHeight();
                    }, this);
                },
                onResize: function() {
                    this.adjustRowHeight();
                },
                onItemClick: function(id) {
                    window.location = this.config.moduleUrl + '&rid=' + id.row;
                }
            }
        });
    });
</script>
