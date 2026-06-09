!include "WinVer.nsh"

!macro NSIS_HOOK_PREINSTALL
  ${IfNot} ${AtLeastWin10}
    MessageBox MB_ICONSTOP "This application requires Windows 10 version 1809 or later.$\r$\n$\r$\nWindows 7 and Windows 8 are not supported.$\r$\n$\r$\nЭто приложение требует Windows 10 (версия 1809) или новее.$\r$\nWindows 7 и 8 не поддерживаются."
    Abort
  ${EndIf}
!macroend
