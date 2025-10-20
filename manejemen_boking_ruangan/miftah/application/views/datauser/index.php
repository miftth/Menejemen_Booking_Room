<table border="1" width="80%" cellpadding="5" cellspacing="0" >
    <tr>
        <td>No</td>
        <td>ID</td>
        <td>Nama Jabatan</td>
        <td>Actions</td>
    </tr><?php $no=1;
    foreach($getdatajabatan as $jabatan){ ?>
    <tr>
        <td><?= $no++ ; ?></td>
        <td><?= $jabatan['id']; ?></td>
        <td><?= $jabatan['nama_jabatan']; ?></td>
        <td><a href="#">Update</a>
        <a href="#">Delete</a></td>
    </tr><?php    
    } ?>
</table>