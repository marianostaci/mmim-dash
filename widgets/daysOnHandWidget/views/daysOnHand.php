<?php
/**
 * Created by Ascendro Web Technologies SRL
 * User: mariano
 * Date: 5/10/13
 * Time: 11:10 AM
 */
?>
<div class="widgetContent">

    <header class="boxHeader clear">
        <h4 class="title">Days on hand</h4>
    </header>

    <div class="table-responsive">
        <table class="newTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Days</th>
                </tr>
            </thead>
            <tbody>
            <?php if( !empty( $data ) ): ?>
                <?php foreach ($data as  $item) : ?>
                    <tr>
                        <td>
                            <?php echo CHtml::link($item['packageName'], array("/inventory/inventory/", "dashboardPID" => $item['productID'])); ?>
                        </td>
                        <td>
                            <?php echo $item['days'].' days'; ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php else : ?>
                <tr class="noHover">
                    <td colspan="2">
                        No results found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>