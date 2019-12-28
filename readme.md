# DEPLOY  
可以不用Fork了，直接点下面图标（可能需要先登录heroku）就可以生成app了。  
[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)  
但更新的时候就不能更新git再去heroku里重新Deploy了，要重新安装。

# DEMO  
https://herooneindex.herokuapp.com/  

# 在Config Vars设置：  
APIKey         ：必填，heroku的API Key，注意有无空格。  
refresh_token  ：必填，微软OFFICE的refresh_token，程序自动添加在环境变量，方便更新版本。 

admin          ：管理密码，不添加时不显示登录页面且无法登录。  
sitename       ：网站的名称，不添加会显示为‘请在环境变量添加sitename’。  
adminloginpage ：管理登录的页面不再是'?admin'，而是此设置的值。如果设置，登录按钮及页面隐藏。  
//public_path    ：使用API长链接访问时，显示网盘文件的路径，不设置时默认为根目录；  
//           　　　不能是private_path的上级（public看到的不能比private多，要么看到的就不一样）。  
//private_path   ：使用自定义域名访问时，显示网盘文件的路径，不设置时默认为根目录。  
//domain_path    ：格式为a1.com:/dir/path1|b1.com:/path2，比private_path优先。  
imgup_path     ：设置图床路径，不设置这个值时该目录内容会正常列文件出来，设置后只有上传界面，不显示其中文件（登录后显示）。  
passfile       ：自定义密码文件的名字，可以是'pppppp'，也可以是'aaaa.txt'等等；  
        　       密码是这个文件的内容，可以空格、可以中文；列目录时不会显示，只有知道密码才能查看或下载此文件。  
