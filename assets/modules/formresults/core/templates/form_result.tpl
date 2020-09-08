<div class="tab-page" id="tab_main">
    <h2 class="tab">
        <?= $form['caption'] ?>
    </h2>

    <script type="text/javascript">
        tpFormResults.addTabPage(document.getElementById('tab_main'));
    </script>

    <div class="row">
        <div class="table-responsive">
            <table class="table data">
                <thead>
                    <tr>
                        <td><?= $_lang['formresults.field_name'] ?></td>
                        <td><?= $_lang['formresults.value'] ?></td>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>ID:</td>
                        <td><?= $result['id'] ?></td>
                    </tr>

                    <tr>
                        <td><?= $_lang['formresults.datetime'] ?>:</td>
                        <td><?= $result['created_at'] ?></td>
                    </tr>
                    
                    <?php foreach ($form['fields'] as $field => $options): ?>
                        <tr>
                            <td><?= htmlspecialchars($options['caption']) ?>:</td>
                            <td>
                                <?php if (!empty($options['type']) && $options['type'] == 'file'): ?>
                                    <?php if (!empty($result['file_' . $field])): ?>
                                        <?= $result['file_' . $field] ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($result[$field]) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
