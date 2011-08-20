    
    <h2>Recent Messages</h2>
    
    <!-- List of message here. Use a partial -->
    <ol>
    <?php foreach ($recent as $k => $v): ?>
        <li>
            <?php echo $v['message']; ?> <span style="color: lightgray">(<?php echo $k; ?>)</span><br/>
            <a href="/messages/<?php echo $k; ?>" style="font-size:10px;">posted at <?php echo $v['created_at']; ?></a>
        </li>
    <?php endforeach; ?>
    </ol>
    <a href="messages/new">New Message</a>
