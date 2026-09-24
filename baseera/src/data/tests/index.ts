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
import { sensitiveTest } from './sensitive-test.ts';
import { eqTest } from './eq-test.ts';
import { careerTest } from './career-test.ts';
import { communicationStyle } from './communication-style.ts';
import { timeManagement } from './time-management.ts';
import { productivityTest } from './productivity-test.ts';
import { decisionMaking } from './decision-making.ts';
import { curiosityTest } from './curiosity-test.ts';
import { thinkingStyle } from './thinking-style.ts';
import { workStyle } from './work-style.ts';
import { attachmentStyle } from './attachment-style.ts';
import { habitStrength } from './habit-strength.ts';
import { moneyRelationship } from './money-relationship.ts';
import { financialLiteracy } from './financial-literacy.ts';
import { riskTolerance } from './risk-tolerance.ts';
import { cognitiveBiases } from './cognitive-biases.ts';
import { logicalReasoning } from './logical-reasoning.ts';
import { numericalReasoning } from './numerical-reasoning.ts';
import { verbalReasoning } from './verbal-reasoning.ts';
import { spatialReasoning } from './spatial-reasoning.ts';
import { workingMemory } from './working-memory.ts';
import { focusTest } from './focus-test.ts';

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
	sensitiveTest,
	eqTest,
	careerTest,
	communicationStyle,
	timeManagement,
	productivityTest,
	decisionMaking,
	curiosityTest,
	thinkingStyle,
	workStyle,
	attachmentStyle,
	habitStrength,
	moneyRelationship,
	financialLiteracy,
	riskTolerance,
	cognitiveBiases,
	logicalReasoning,
	numericalReasoning,
	verbalReasoning,
	spatialReasoning,
	workingMemory,
	focusTest,
];

export const testBySlug = new Map( tests.map( ( t ) => [ t.slug, t ] ) );
