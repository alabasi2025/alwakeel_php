# 🔑 SSH Keys للنشر على Hostinger

## المفاتيح:
- `alwakeel_hostinger_key` - المفتاح الخاص (Private Key)
- `alwakeel_hostinger_key.pub` - المفتاح العام (Public Key)

## الاستخدام:

### للنشر على Hostinger عبر SFTP:
```bash
sftp -i .ssh/alwakeel_hostinger_key -P 65002 u306850950@156.67.218.125
```

### للنشر عبر SCP:
```bash
scp -i .ssh/alwakeel_hostinger_key -P 65002 -r * u306850950@156.67.218.125:/home/u306850950/domains/mediumturquoise-porcupine-839487.hostingersite.com/public_html/
```

## ⚠️ تحذير:
- لا تشارك المفتاح الخاص مع أحد
- المفتاح العام موجود بالفعل على Hostinger

## 📝 معلومات الاتصال:
- **Host:** 156.67.218.125
- **Port:** 65002
- **User:** u306850950
- **Path:** /home/u306850950/domains/mediumturquoise-porcupine-839487.hostingersite.com/public_html/
