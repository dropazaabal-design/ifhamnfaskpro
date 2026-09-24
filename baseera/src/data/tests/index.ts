/**
 * سجلّ الاختبارات المنشورة.
 *
 * ما يُسجَّل هنا وحده يُبنى صفحةً ويدخل خريطة الموقع. الاختبارات «القادمة»
 * في الكتالوج لا تُولَّد لها صفحات: صفحة «قريباً» فارغة محتوى رقيق يضرّ
 * الموقع في البحث وفي قبول AdSense معاً.
 */
import type { Test } from '../../lib/types.ts';
import { bigFive } from './big-five.ts';
import { introvertExtrovert } from './introvert-extrovert.ts';
import { resilienceTest } from './resilience-test.ts';
import { anxietyScale } from './anxiety-scale.ts';
import { burnoutAssessment } from './burnout-assessment.ts';
import { geniusTest } from './genius-test.ts';
import { iqTest } from './iq-test.ts';
import { selfDiscipline } from './self-discipline.ts';
import { selfEsteem } from './self-esteem.ts';
import { brainrotTest } from './brainrot-test.ts';

export const tests: Test[] = [
	bigFive,
	introvertExtrovert,
	resilienceTest,
	anxietyScale,
	burnoutAssessment,
	geniusTest,
	iqTest,
	selfDiscipline,
	selfEsteem,
	brainrotTest,
];

export const testBySlug = new Map( tests.map( ( t ) => [ t.slug, t ] ) );
