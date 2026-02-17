<h2>Add New Artwork</h2>

<form action="<?php echo BASE_URL; ?>index.php?controller=admin&action=addArtwork" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Title*</label>
        <input type="text" name="title" id="title" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" class="form-control"></textarea>
    </div>
    
    <div class="form-group">
        <label for="price">Price ($)*</label>
        <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" required>
    </div>
    
    <div class="form-group">
        <label for="artist_id">Artist*</label>
        <select name="artist_id" id="artist_id" class="form-control" required>
            <option value="">Select an Artist</option>
            <?php if (isset($artists) && $artists->num_rows > 0): ?>
                <?php while ($artist = $artists->fetch_assoc()): ?>
                    <option value="<?php echo $artist['id']; ?>"><?php echo $artist['name']; ?></option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="category_id">Category</label>
        <select name="category_id" id="category_id" class="form-control">
            <option value="">Select a Category</option>
            <?php if (isset($categories) && $categories->num_rows > 0): ?>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="image">Artwork Image*</label>
        <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-success">Add Artwork</button>
        <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=artworks" class="btn btn-primary">Cancel</a>
    </div>
</form> 