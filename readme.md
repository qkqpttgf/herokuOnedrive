# DEPLOY
[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

# DEMO  
https://herooneindex.herokuapp.com/  

# 在Config Vars设置：  
sitename       ：网站的名称，不添加会显示为‘请在环境变量添加sitename’。  
admin          ：管理密码，不添加时不显示登录页面且无法登录。  
adminloginpage ：管理登录的页面不再是'?admin'，而是此设置的值。如果设置，登录按钮及页面隐藏。  
public_path    ：使用API长链接访问时，显示网盘文件的路径，不设置时默认为根目录；  
           　　　不能是private_path的上级（public看到的不能比private多，要么看到的就不一样）。  
private_path   ：使用自定义域名访问时，显示网盘文件的路径，不设置时默认为根目录。  
domain_path    ：格式为a1.com=/dir/path1&b1.com=/path2，比private_path优先。  
imgup_path     ：设置图床路径，不设置这个值时该目录内容会正常列文件出来，设置后只有上传界面，不显示其中文件（登录后显示）。  
passfile       ：自定义密码文件的名字，可以是'pppppp'，也可以是'aaaa.txt'等等；  
        　       密码是这个文件的内容，可以空格、可以中文；列目录时不会显示，只有知道密码才能查看或下载此文件。  
refresh_token  ：把refresh_token放在环境变量，方便更新版本。 
