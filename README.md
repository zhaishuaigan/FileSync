FileSync
========

网站文件同步<br />

此程序可以实现网站的图片资源同步备份, 多主多从的方式运行.<br />
<br />
提供接口有:<br />
1. 上传文件接口:<br />
　　upload.php  提交给这个文件的文件域必须是name="file", 提交到这个地址的文件会自动同步到其他服务器<br />
　　返回值{"success":1,"link":"y/m/d/guid.jpg"}<br />
2. 获取文件接口:<br />
　　index.php   获取图片接口<br />
　　http://xxx.xxx.xxx/index.php/size/y/m/d/filename.png<br />
3. 同步文件接口<br />
　　sync.php  // 内部调用<br />
4. 获取原图接口:<br />
　　get.php  //  内部调用<br />