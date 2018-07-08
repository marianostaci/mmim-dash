<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mariano
 * Date: 6/6/13
 * Time: 9:11 AM
 * To change this template use File | Settings | File Templates.
 */
?>
<div class="widgetContent">

    <header class="boxHeader clear">
        <h4 class="title">Total sales</h4>
    </header>

    <div class="table-responsive">
        <table class="newTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Sales</th>
                    <th>Returns</th>
                    <th>Net</th>
                <tr>
            </thead>
            <tbody>
                <?php if (!empty( $rows )): ?>
                    <?php  foreach( $rows as $row ): ?>
                        <tr>
                            <td><?php echo $row[ 'product' ]; ?></td>
                            <td>
                                <?php echo $row[ 'sales' ]; ?>
                                <br>
                                <?php echo $row[ 'salesTrend' ]; ?>
                            </td>
                            <td>
                                <?php echo $row[ 'returns' ]; ?>
                                <br>
                                <?php echo $row[ 'returnsTrend' ]; ?>
                            </td>
                            <td><?php echo $row[ 'net' ]; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else :?>
                <tr class="noHover">
                    <td colspan="6">
                        No results found.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>