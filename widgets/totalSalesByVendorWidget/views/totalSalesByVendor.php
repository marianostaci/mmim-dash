<?php
/**
 * Created by Ascendro Web Technologies SRL
 * User: mariano
 * Date: 8/13/13
 * Time: 1:55 PM
 */
?>
<div class="widgetContent">
    <header class="boxHeader clear">
	    <h4 class="title">Sales by Vendor</h4>
    </header>

    <div class="table-responsive">
		<table class="newTable">
			<thead>
                <tr>
                    <th>Vendor</th>
                    <th>Sales</th>
                    <th></th>
                    <th>Returns</th>
                    <th></th>
                    <th>Net</th>
                </tr>
			</thead>
			<tbody>
			<?php if (!empty( $rows )): ?>
				<?php  foreach( $rows as $row ): ?>
				<tr>
					<td><?php echo $row[ 'vendor' ]; ?></td>
					<td><?php echo $row[ 'sales' ]; ?></td>
					<td><?php echo $row[ 'salesTrend' ]; ?></td>
					<td><?php echo $row[ 'returns' ]; ?></td>
					<td><?php echo $row[ 'returnsTrend' ]; ?></td>
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