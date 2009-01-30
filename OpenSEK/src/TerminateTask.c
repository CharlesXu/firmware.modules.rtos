/* Copyright 2008, Mariano Cerdeiro
 *
 * This file is part of OpenSEK.
 *
 * OpenSEK is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OpenSEK is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OpenSEK. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/** \brief OpenSEK TerminateTask Implementation File
 **
 ** This file implements the TerminateTask API
 **
 ** \file TerminateTask.c
 **
 **/

/** \addtogroup OpenSEK
 ** @{ */
/** \addtogroup OpenSEK_Global
 ** @{ */


/*
 * Initials     Name
 * ---------------------------
 * MaCe         Mariano Cerdeiro
 * KLi          Kang Li
 */

/*
 * modification history (new versions first)
 * -----------------------------------------------------------
 * 20090130 v0.1.3 MaCe add OSEK_MEMMAP check
 * 20081113 v0.1.1 KLi  Added memory layout attribute macros
 * 20080810 v0.1.0 MaCe initial version
 */

/*==================[inclusions]=============================================*/
#include "Osek_Internal.h"

/*==================[macros and definitions]=================================*/

/*==================[internal data declaration]==============================*/

/*==================[internal functions declaration]=========================*/

/*==================[internal data definition]===============================*/

/*==================[external data definition]===============================*/

/*==================[internal functions definition]==========================*/

/*==================[external functions definition]==========================*/
#if (OSEK_MEMMAP == ENABLE)
#define OpenSEK_START_SEC_CODE
#include "MemMap.h"
#endif

StatusType TerminateTask
(
	void
)
{
	/* \req OSEK_SYS_3.2 The system service StatusType TerminateTask ( void )
	 ** shall be defined. */

	StatusType ret = E_OK;

	/* \req OSEK_SYS_3.2.4 If the version with extended status is used, the
	 ** service returns in case of error, and provides a status which can be
	 ** evaluated in the application. */
#if (ERROR_CHECKING_TYPE == ERROR_CHECKING_EXTENDED)
	if ( GetCallingContext() != CONTEXT_TASK )
	{
		/* \req OSEK_SYS_3.2.7-1/2 Possibly return values in Extended mode are
		 ** E_OS_RESOURCE or E_OS_CALLEVEL */
		ret = E_OS_CALLEVEL;
	}
	/* check if on or more resources are ocupied */
	else if ( TasksVar[GetRunningTask()].Resources != 0 )
	{
		/* \req OSEK_SYS_3.2.7-2/2 Possibly return values in Extended mode are
		 ** E_OS_RESOURCE or E_OS_CALLEVEL */
		ret = E_OS_RESOURCE;
	}
	else
#endif
	{

		IntSecure_Start();

		/* release internal resources */
		/* \req OSEK_SYS_3.2.2 If an internal resource is assigned to the calling
		 ** task, it is automatically released. */
		ReleaseInternalResources();

		/* decrement activations for this task */
		TasksVar[GetRunningTask()].Activations--;

		if (TasksVar[GetRunningTask()].Activations == 0)
		{
			/* if no more activations set state to suspended */
			/* \req OSEK_SYS_3.2.1 The calling task shall be transferred from the
			 ** running state into the suspended state. */
			TasksVar[GetRunningTask()].Flags.State = TASK_ST_SUSPENDED;
		}
		else
		{
			/* if more activations set state to ready */
         TasksVar[GetRunningTask()].Flags.State = TASK_ST_READY;
		}

		/* set entry point for this task again */
		/* \req OSEK_SYS_3.1.2-3/3 The operating system shall ensure that the task
		 ** code is being executed from the first statement. */
		SetEntryPoint(GetRunningTask());
		/* remove ready list */
		RemoveTask(GetRunningTask());
		/* set running task to invalid */
		SetRunningTask(INVALID_TASK);

		IntSecure_End();

		/* call scheduler, never returns */
		/* \req OSEK_SYS_3.2.3 If the call was successful, TerminateTask does not
		 ** return to the call level and the status can not be evaluated. */
		/* \req OSEK_SYS_3.2.5 If the service TerminateTask is called
		 ** successfully, it enforces a rescheduling. */
		/* \req OSEK_SYS_3.2.6 This function will never returns in Standard
		 ** mode. */
		(void)Schedule();
	}

#if (HOOK_ERRORHOOK == ENABLE)
	/* \req OSEK_ERR_1.3-2/xx The ErrorHook hook routine shall be called if a
	 ** system service returns a StatusType value not equal to E_OK.*/
	/* \req OSEK_ERR_1.3.1-2/xx The hook routine ErrorHook is not called if a
	 ** system service is called from the ErrorHook itself. */
	if ( ( ret != E_OK ) && (ErrorHookRunning != 1))
	{
		SetError_Api(OSServiceId_TerminateTask);
		SetError_Ret(ret);
		SetError_Msg("Terminate Task returns != than E_OK");
		SetError_ErrorHook();
	}
#endif

	return ret;
}

#if (OSEK_MEMMAP == ENABLE)
#define OpenSEK_STOP_SEC_CODE
#include "MemMap.h"
#endif

/** @} doxygen endVar group definition */
/** @} doxygen endVar group definition */
/*==================[end of file]============================================*/
