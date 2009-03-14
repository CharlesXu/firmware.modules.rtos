/* Copyright 2008, 2009, Mariano Cerdeiro
 *
 * This file is part of OpenSEK.
 *
 * OpenSEK is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *             
 * Linking OpenSEK statically or dynamically with other modules is making a
 * combined work based on OpenSEK. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * In addition, as a special exception, the copyright holders of OpenSEK give
 * you permission to combine OpenSEK program with free software programs or
 * libraries that are released under the GNU LGPL and with independent modules
 * that communicate with OpenSEK solely through the OpenSEK defined interface. 
 * You may copy and distribute such a system following the terms of the GNU GPL
 * for OpenSEK and the licenses of the other code concerned, provided that you
 * include the source code of that other code when and as the GNU GPL requires
 * distribution of source code.
 *
 * Note that people who make modified versions of OpenSEK are not obligated to
 * grant this special exception for their modified versions; it is their choice
 * whether to do so. The GNU General Public License gives permission to release
 * a modified version without this exception; this exception also makes it
 * possible to release a modified version which carries forward this exception.
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

/** \brief OpenSEK Internal ARCH CPU Dependece Implementation File
 **
 ** \file arm/Osek_Internal_Arch_Cpu.c
 ** \arch arm/lpc2xxx
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
 * 20090227 v0.1.0 MaCe initial version
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
void StartOs_Arch_Cpu
(
	void
)
{
	T0CTCR = 0b00;	/* bit 1-0: 00 Timer mode
										01 Counter mode at rising edge
										10 Counter mode at falling edge
										11 Counter mode both edges
							bit 3-2: only valid when bit 1-0 != 0
										00 CAPn.0 for timer n
										01 CAPn.1 for timer n
										1x reserved
							bit 7-4: reserved
						*/

	T0PR = 12;		/* 32-bits prescaler register */
						/* 1 incremenet every 1us */

	/* set Timer Control Register TCR */
	T0TCR = 0b11;	/* bit 0:	enable counter
							bit 1:	reset counter
							bit 7-2: reserved */

	T0TCR = 0b01;	/* bit 1:	clear reset now */

	T0MR0 = 1000;	/* timer match 0 every 1ms*/

	T0MCR = 0b11;	/* bit 0: interrupt if MR0 match the TC
							bit 1: reset TC if MR0 match */

	/* enable interrupts */
	__asm__ __volatile__
	("											\
		MRS R7, CPSR 				\n\t	\
		AND R7, R7, #0xFFFFFF9F \n\t	\
		MSR CPSR, R7				\n\t	\
	 " : : : "r7" );

	/* set the TIMER0 as FIQ Interrupt */
	((VICType*)VIC_BASE_ADDR)->IntSelect |= 1<<4;

	/* enable TIMER0 interrupt */
	((VICType*)VIC_BASE_ADDR)->IntEnable |= 1<<4;

}

/** @} doxygen end group definition */
/** @} doxygen end group definition */
/*==================[end of file]============================================*/

