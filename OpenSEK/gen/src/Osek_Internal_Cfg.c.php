/********************************************************
 * DO NOT CHANGE THIS FILE, IT IS GENERATED AUTOMATICALY*
 ********************************************************/

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

/** \brief OpenSEK Generated Internal Configuration Implementation File
 **
 **
 **
 ** \file Osek_Internal_Cfg.c
 **
 **/

/** \addtogroup OpenSEK
 ** @{ */
/** \addtogroup OpenSEK_Internal
 ** @{ */

/*
 * Initials     Name
 * ---------------------------
 * MaCe         Mariano Cerdeiro
 */

/*
 * modification history (new versions first)
 * -----------------------------------------------------------
 * 20080713 v0.1.0 MaCe       - initial version
 */

/*==================[inclusions]=============================================*/
#include "Osek_Internal.h"

/*==================[macros and definitions]=================================*/

/*==================[internal data declaration]==============================*/

/*==================[internal functions declaration]=========================*/

/*==================[internal data definition]===============================*/
<?php
/* get tasks */
$tasks = $config->getList("/OSEK","TASK");

foreach ($tasks as $task)
{
   print "/** \brief $task stack */\n";
   print "uint8 StackTask" . $task . "[" . $config->getValue("/OSEK/" . $task, "STACK") ."];\n";
}
print "\n";

foreach ($tasks as $task)
{
   print "/** \brief $task context */\n";
   print "TaskContextType ContextTask" . $task . ";\n";
}
print "\n";

/* Ready List */
foreach ($priority as $prio)
{
   print "/** \brief Ready List for Priority $prio */\n";
	$count = 0;
	foreach ($tasks as $task)
	{
		if ($priority[$config->getValue("/OSEK/" . $task, "PRIORITY")] == $prio)
		{
			$count += $config->getValue("/OSEK/" . $task, "ACTIVATION");
		}
	}
	print "TaskType ReadyList" . $prio . "[" . $count . "];\n\n";
}

$counters = $config->getList("/OSEK","COUNTER");
$alarms = $config->getList("/OSEK","ALARM");
foreach ($counters as $counter)
{
	$countalarms = 0;
	foreach ($alarms as $alarm)
   {
		if ($counter == $config->getValue("/OSEK/" . $alarm,"COUNTER"))
      {
         $countalarms++;
		}
	}
	print "const AlarmType OSEK_ALARMLIST_" . $counter . "[" . $countalarms . "] = {\n";
	foreach ($alarms as $alarm)
	{
		if ($counter == $config->getValue("/OSEK/" . $alarm,"COUNTER"))
		{
			print "	$alarm, /* this alarm has to be incremented with this counter */\n";
		}
	}
	print "};\n\n";
}

?>

/*==================[external data definition]===============================*/
#define OpenSEK_START_SEC_DATA
#include "MemMap.h"

/* OpenSEK to configured priority table
 *
 * This table show the relationship between the user selected
 * priorities and the OpenOSE priorities:
 *
 * User P.			Osek P.
<?php
$pkey = array_keys($priority);
for($i = 0; $i < count($priority); $i++)
{
	print " * " . $pkey[$i] . "					" . $priority[$pkey[$i]] . "\n";
}
print " */\n";

?>

const TaskConstType TasksConst[TASKS_COUNT] = {
<?php
$count = 0;
/* create task const structure */
foreach ($tasks as $task)
{
	if ( $count++ != 0 ) print ",\n";
	print "	/* Task $task */\n";
	print "	{\n";
	print " 		OSEK_TASK_$task,	/* task entry point */\n";
	print "		&ContextTask" . $task . ", /* pointer to task context */\n";
	print "		StackTask" . $task . ", /* pointer stack memory */\n";
	print "		" . $config->getValue("/OSEK/" . $task, "STACK") . ", /* stack size */\n";
	print "		" . $priority[$config->getValue("/OSEK/" . $task, "PRIORITY")] . ", /* task priority */\n";
	print "		" . $config->getValue("/OSEK/" . $task, "ACTIVATION"). ", /* task max activations */\n";
	print	"		{\n";
	$extended = $config->getValue("/OSEK/" . $task, "TYPE");
	if($extended == "BASIC")
	{
		print	"			0, /* basic task */\n";
	}
	elseif ($extended == "EXTENDED")
	{
		print "			1, /* extended task */\n";
	}
	else
	{
		error("Wrong definition of task type \"" . $extended . "\" for task \"" . $task . "\".");
	}
	$schedule = $config->getValue("/OSEK/" .$task, "SCHEDULE");
	if ($schedule == "FULL")
	{
		print	"			1, /* preemtive task */\n";
	}
	elseif($schedule == "NON")
	{
		print "			0, /* non preemtive task */\n";
	}
	else
	{
		error("Wrong definition of task schedule \"" . $schedule . "\" for task \"" . $task . "\".");
	}
	print "			0\n";
	print "		}, /* task const flags */\n";
	$events = $config->getList("/OSEK/" . $task, "EVENT");
	$elist = "0 ";
	foreach ($events as $event)
	{
		$elist .= "| $event ";
	}
	print "		$elist, /* events mask */\n";
	$rlist = "0 ";
	$resources = $config->getList("/OSEK/" . $task, "RESOURCE");
	foreach($resources as $resource)
	{
		$rlist .= "| ( 1 << $resource ) ";
	}
	print "		$rlist/* resources mask */\n";
	print "	}";
}
print "\n";
?>
};

/** \brief TaskVar Array */
TaskVariableType TasksVar[TASKS_COUNT];

<?php
$appmodes = $config->getList("/OSEK", "APPMODE");

foreach ($appmodes as $appmode)
{
	$tasksinmode = array();
	foreach($tasks as $task)
	{
		$taskappmodes = $config->getList("/OSEK/" . $task, "APPMODE");
		foreach ($taskappmodes as $taskappmode)
		{
			if ($taskappmode == $appmode)
			{
				$tasksinmode[] = $task;
			}
		}
	}
	if (count($tasksinmode) > 0)
	{
		$count = 0;
		print "/** \brief List of Auto Start Tasks in Application Mode $appmode */\n";
		print "const TaskType TasksAppMode" . $appmode . "[" . count($tasksinmode). "]  ATTRIBUTES() = {\n";
		foreach($tasksinmode as $task)
		{
			if ($count++ != 0) print ",\n";
			print "	$task";
		}
		print "\n};\n";
	}
}

print "/** \brief AutoStart Array */\n";
print "const AutoStartType AutoStart[" . count($appmodes) . "]  ATTRIBUTES() = {\n";
$count = 0;
foreach ($appmodes as $appmode)
{
	if ( $count++ != 0 ) print ",\n";
	print "	/* Application Mode $appmode */\n";
	print "	{\n";
	$tasksinmode = array();
	foreach($tasks as $task)
	{
		$taskappmodes = $config->getList("/OSEK/" . $task, "APPMODE");
		foreach ($taskappmodes as $taskappmode)
		{
			if ($taskappmode == $appmode)
			{
				$tasksinmode[] = $task;
			}
		}
	}
	print "		" . count($tasksinmode) .", /* Total Auto Start Tasks in this Application Mode */\n";
	if (count($tasksinmode)>0)
	{
		print "		(TaskRefType)TasksAppMode" . $appmode . " /* Pointer to the list of Auto Start Stacks on this Application Mode */\n";
	}
	else
	{
		print "		NULL /* no tasks on this mode */\n";
	}
	print "	}";
}
print "\n};\n";
?>

<?php
	print "const ReadyConstType ReadyConst[" . count($priority) .  "] = { \n";
	$c = 0;
	foreach ($priority as $prio)
	{
		if ($c++ != 0) print ",\n";
		print "	{\n";
		$count = 0;
		foreach ($tasks as $task)
	   {
   	   if ($priority[$config->getValue("/OSEK/" . $task, "PRIORITY")] == $prio)
	      {
   	      $count += $config->getValue("/OSEK/" . $task, "ACTIVATION");
      	}
	   }
		print "		$count, /* Length of this ready list */\n";
		print "		ReadyList" . $prio . " /* Pointer to the Ready List */\n";
		print "	}";
	}
	print "\n};\n\n";

print "/** TODO replace next line with: \n";
print " ** ReadyVarType ReadyVar[" . count($priority) . "]  ATTRIBUTES(); */\n";
print "ReadyVarType ReadyVar[" . count($priority) . "];\n";
?>

<?php
/* Resources Priorities */
$resources = $config->getList("/OSEK","RESOURCE");
print "/** \brief Resources Priorities */\n";
print "const TaskPriorityType ResourcesPriority[" . count($resources) . "]  ATTRIBUTES() = {\n";
$c = 0;
foreach ($resources as $resource)
{
	$count = 0;
	foreach ($tasks as $task)
	{
		$resorucestask = $config->getList("/OSEK/" . $task, "RESOURCE");
		foreach($resorucestask as $rt)
		{
			if ($rt == $resource)
			{
				if ($priority[$config->getValue("/OSEK/" . $task, "PRIORITY")] > $count)
				{
					$count = $priority[$config->getValue("/OSEK/" . $task, "PRIORITY")];
				}
			}
		}
	}
	if ($c++ != 0)	print ",\n";
	print "	$count";
	
}
print "\n};\n";

$alarms = $config->getList("/OSEK","ALARM");

print "/** TODO replace next line with: \n";
print " ** AlarmVarType AlarmsVar[" . count($alarms) . "] ATTRIBUTES(); */\n";
print "AlarmVarType AlarmsVar[" . count($alarms) . "];\n\n";

print "const AlarmConstType AlarmsConst[" . count($alarms) . "]  ATTRIBUTES() = {\n";
$count = 0;
foreach ($alarms as $alarm)
{
	if ($count++ != 0)
	{
		print ",\n";
	}
	print "	{\n";
	print	"		OSEK_COUNTER_" . $config->getValue("/OSEK/" . $alarm, "COUNTER") . ", /* Counter */\n";
	$action = $config->getValue("/OSEK/" . $alarm, "ACTION");
	print "		" . $action . ", /* Alarm action */\n";
	print "		{\n";
	switch ($action)
	{
		case "INCREMENT":
			print "			NULL, /* no callback */\n";
			print "			0, /* no task id */\n";
			print "			0, /* no event */\n";
			print "			OSEK_COUNTER_" . $config->getValue("/OSEK/" . $alarm . "/INCREMENT","COUNTER") . " /* counter */\n";
			break;
		case "ACTIVATETASK":
			print "			NULL, /* no callback */\n";
			print "			" . $config->getValue("/OSEK/" . $alarm . "/ACTIVATETASK","TASK") . ", /* TaskID */\n";
			print "			0, /* no event */\n";
			print "			0 /* no counter */\n";
			break;
		case "SETEVENT":
			print "			NULL, /* no callback */\n";
			print "			" . $config->getValue("/OSEK/" . $alarm . "/SETEVENT","TASK") . ", /* TaskID */\n";
			print "			" . $config->getValue("/OSEK/" . $alarm . "/SETEVENT","EVENT") . ", /* no event */\n";
			print "			0 /* no counter */\n";
			break;
		case "ALARMCALLBACK":
         print "			OSEK_CALLBACK_" . $config->getValue("/OSEK/" . $alarm . "/ALARMCALLBACK","CALLBACK") . ", /* callback */\n";
         print "			0, /* no taskid */\n";
			print "			0, /* no event */\n";
			print "			0 /* no counter */\n";
			break;
		default:
			error("Alarm $alarm has an invalid action: $action");
			break;
	}
	print	"		},\n";
	print "	}";
	
}
print "\n};\n\n";

print "const AutoStartAlarmType AutoStartAlarm[ALARM_AUTOSTART_COUNT] = {\n";
$count = 0;
foreach ($alarms as $alarm)
{
   if ($config->getValue("/OSEK/" . $alarm, "AUTOSTART") == "TRUE")
   {
		if ($count++ != 0)
		{
			print ",\n";
		}
		print "  {\n";

		print "		" . $config->getValue("/OSEK/" . $alarm, "APPMODE") . ", /* Application Mode */\n";
		// print "		OSEK_COUNTER_" . $config->getValue("/OSEK/" . $alarm, "COUNTER") . ", /* Counter */\n";
		print "		$alarm, /* Alarms */\n";
		print "		" . $config->getValue("/OSEK/" . $alarm, "ALARMTIME") . ", /* Alarm Time */\n";
		print "		" . $config->getValue("/OSEK/" . $alarm, "CYCLETIME") . " /* Alarm Time */\n";
		print "	}";
   }
}
print "\n};\n\n";

$counters = $config->getList("/OSEK","COUNTER");
print "CounterVarType CountersVar[" . count($counters) . "];\n";

$alarms = $config->getList("/OSEK","ALARM");
$count = 0;
print "const CounterConstType CountersConst[" . count($counters) . "] = {\n";
foreach ($counters as $counter)
{
	if ($count++!=0)
	{
		print ",\n";
	}
	print "	{\n";
	$countalarms = 0;
	foreach ($alarms as $alarm)
	{
		if ($counter == $config->getValue("/OSEK/" . $alarm,"COUNTER"))
		{
			$countalarms++;
		}
	}
	print "		$countalarms, /* quantity of alarms for this counter */\n";
	print "		OSEK_ALARMLIST_" . $counter . ", /* alarms list */\n";
	print "		" . $config->getValue("/OSEK/" . $counter,"MAXALLOWEDVALUE") . ", /* max allowed value */\n";
	print "		" . $config->getValue("/OSEK/" . $counter,"MINCYCLE") . ", /* min cycle */\n";
	print "		" . $config->getValue("/OSEK/" . $counter,"TICKSPERBASE") . " /* ticks per base */\n";
	print	"	}";
}
print "\n};\n\n";

?>

/** TODO replace the next line with 
 ** uint8 ApplicationMode ATTRIBUTES(); */
uint8 ApplicationMode;

/** TODO replace the next line with
 ** uint8 ErrorHookRunning ATTRIBUTES(); */
uint8 ErrorHookRunning;

#define OpenSEK_STOP_SEC_DATA
#include "MemMap.h"

/*==================[internal functions definition]==========================*/

/*==================[external functions definition]==========================*/
#define OpenSEK_START_SEC_CODE
#include "MemMap.h"

<?php
$intnames = $config->getList("/OSEK","ISR");
foreach ($intnames as $int)
{
	$inttype = $config->getValue("/OSEK/" . $int,"INTERRUPT");
	$intcat = $config->getValue("/OSEK/" . $int,"CATEGORY");

	if ($intcat == 2)
	{
		print "void OSEK_ISR2_$int(void)\n";
		print "{\n";
		print "	PreIsr2($int);\n";
		print "	OSEK_ISR_$int()\n";
		print "	PostIsr2($int);\n";
		print "}\n";
	}
	
}
?>

#define OpenSEK_STOP_SEC_CODE
#include "MemMap.h"

/** @} doxygen end group definition */
/** @} doxygen end group definition */
/*==================[end of file]============================================*/

