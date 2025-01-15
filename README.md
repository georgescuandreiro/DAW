# DAW
### Proiect DAW - revista online - echonewsmagazine
### Link Site: https://echonewsmagazine.iceiy.com/
### User Admin: echonewsletter@yahoo.com

### reCaptcha v2 Admin Console - https://www.google.com/recaptcha/admin/site/714769590

```
Pagini admini:

https://echonewsmagazine.iceiy.com/pages/manage_users
https://echonewsmagazine.iceiy.com/pages/admin_dashboard
```

# Taskuri terminate:

```
1. Proiectul va utiliza o baza de date MySQL si va fi programat în PHP. - DONE 
	> baza de date MySQL - aeonfree ( hosting gratuit + certificat SSL )
	> cod PHP hostat pe aeonfree 
2. Prin intermediul aplicatiei se vor efectua operatii de stergere, adaugare, citire asupra bazei de date. ( OPERATII CRUD ) - DONE 
	> creare utilizator - register user
	> alterare/update parola utilizator - resetare parola 
	> citire din baza de date - interogare daca user exista deja 
	> stergere - token verificare
3. Va exista o pagina de autentificare/înregistrare de utilizatori. - DONE 
	> pagina de autentificare - login.php
	> pagina de inregistrare - register.php
4. Vor exista mai multe categorii de utilizatori. Fiecare categorie va avea anumite actiuni specifice. - DONE
	> admin user - adaugat in tabelul de useri coloana is_admin
	> make admin/creator do custom stuff / admin_dashboard + manage_users - paginile adminilor 
- add creator user (optional) 
5. Aplicatia va contine mai multe pagini dinamice cu legaturi între ele. - DONE 
	> pagina de login (exemplu)
		> poate duce la register 
		> poate duce la reset password
		> poate duce la dashboard
6. Va exista posibilitatea de generare si vizualizare de rapoarte (nu doar HTML/PHP/CSV). - DONE
		> fpdf pentru export pdf - folosit pentru manage_users > buton export pdf
- export la analitice - (optional)

7. Elemente statistice ale site-ului (website analytics): vizitatori, accesari etc. - DONE
		> add website analytics - cod in header, tabel in baza de date (analytics), afisare statistica in dashboard_admin 
		> jgraph pentru grafuri
8. Formular de contact cu posibilitatea de a transmite email-uri. - DONE 
	> pagina de contact
9. Integrarea de informatii (nu pagini, elemente ale acestora – parsare continut) din surse externe. - DONE 
	> folosit rss de la BBC pentru parsare continut din sursa externa (nu recomandat)
	> de folosit si alte tipuri de parsare: html ( parsare html din wikipedia pe pagina about_us )
10. Terminarea sesiunii. - DONE 
	> pagina logout.php
    > terminare sesiuni pe pagini
```

### Notite
```
1. Serverul SMTP folosit = yahoo gratuit (se misca cam lent + limitare 500 emailuri pe zi)
2. Emailul pentru contact = echonewsletter@yahoo.com
3. Pentru login reCAPTCHA este activat dupa 3 incercari esuate / pentru restul formularelor este afisat din prima
4. Din pagina pages/manage_users se pot gestiona userii  
5. Din pagina pages/admin_dashboard se pot vizualiza grafurile generate cu jpgraph cu date din tabelul analytics
6. Pentru securitate am adaugat reguli in root folder .htaccess / prin alte foldere pentru siguranta
 > mentiune mascarea erorii 404 in 403 
```