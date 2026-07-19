@echo off
echo ========================================
echo   DEPLOIEMENT FACTPRO -> LWS
echo ========================================
echo.
echo Connexion SSH au serveur...
ssh inter1011016@factpro.ibigsoft.com "cd ~/www && echo '=== GIT PULL ===' && git pull origin master && echo '=== CLEAR CACHE ===' && php artisan view:clear && php artisan config:clear && php artisan route:clear && echo '=== DEPLOY OK ==='"
echo.
if %ERRORLEVEL% EQU 0 (
    echo SUCCES : Serveur mis a jour !
) else (
    echo ECHEC : Verifiez votre connexion SSH.
)
pause
