<?php

namespace Database\Seeders;

use App\Models\Quiz;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * The learning content for Feature 6. Safe to run again: it updates in place.
 * Each question: [question, [4 options], index of the correct option (0-3), explanation]
 */
class QuizSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->quizzes() as $data) {
            $quiz = Quiz::updateOrCreate(['slug' => $data['slug']], Arr::except($data, ['questions']));

            $quiz->questions()->delete();

            foreach ($data['questions'] as $position => [$question, $options, $correct, $explanation]) {
                $quiz->questions()->create([
                    'question' => $question,
                    'options' => $options,
                    'correct_index' => $correct,
                    'explanation' => $explanation,
                    'position' => $position,
                ]);
            }
        }
    }

    private function quizzes(): array
    {
        return [
            [
                'slug' => 'saving-smart',
                'title' => 'Saving Smart',
                'description' => 'Why saving matters and how to do it, one taka at a time.',
                'icon' => 'bi-piggy-bank',
                'color' => '#E2136E',
                'questions' => [
                    ['You get ৳100 pocket money. What is a smart first step?',
                        ['Spend it all today', 'Save a part of it first, then spend the rest', 'Lend it all to a friend', 'Hide it and forget about it'],
                        1, 'Saving a part BEFORE spending ("pay yourself first") is a habit that helps you reach your goals.'],
                    ['What is a savings goal?',
                        ['Something you want to save money for', 'A football match', 'A bank fee', 'Money you must spend quickly'],
                        0, 'A savings goal is a target, like a bicycle, that you save toward step by step.'],
                    ['You want a ৳600 cricket bat in 6 weeks. How much should you save each week?',
                        ['৳50', '৳100', '৳200', '৳600'],
                        1, '৳600 divided by 6 weeks = ৳100 per week.'],
                    ['Where is your money safest?',
                        ['In your school bag', 'Under your pillow', 'In a bank account or a trusted wallet app', 'With a stranger'],
                        2, 'Banks and trusted wallet apps keep money safe and record every taka.'],
                    ['Saving ৳10 every day for 30 days gives you...',
                        ['৳30', '৳100', '৳300', '৳3,000'],
                        2, '৳10 × 30 days = ৳300. Small amounts add up!'],
                ],
            ],
            [
                'slug' => 'needs-vs-wants',
                'title' => 'Needs vs Wants',
                'description' => 'Learn to tell what you need from what you just want.',
                'icon' => 'bi-cart-check',
                'color' => '#7C3AED',
                'questions' => [
                    ['Which of these is a NEED?',
                        ['A new video game', 'School notebooks', 'A third cricket ball', 'Fancy stickers'],
                        1, 'Needs are things you must have, like school supplies. Wants are nice extras.'],
                    ['Which of these is a WANT?',
                        ['Drinking water', 'Lunch at school', 'A toy robot', 'A school uniform'],
                        2, 'A toy robot is fun, but you can live without it, so it is a want.'],
                    ['You have ৳200. You need a ৳150 geometry box for exams, but you also like a ৳200 toy. What should you do?',
                        ['Buy the toy', 'Buy the geometry box first', 'Spend it all on snacks', 'Borrow money to buy both'],
                        1, 'Needs come first. You can save up for the toy later.'],
                    ['A good question to ask before buying something is...',
                        ['Is it shiny?', 'Do my friends have it?', 'Do I need it, or do I just want it?', 'Is the shop busy?'],
                        2, 'Asking "need or want?" helps you avoid spending you regret later.'],
                    ['Waiting one day before buying something you want helps because...',
                        ['Prices always go down', 'You get time to decide if you really want it', 'Shops close at night', 'It does not help at all'],
                        1, 'A short wait stops impulse buying. Often the "must have it now" feeling fades.'],
                ],
            ],
            [
                'slug' => 'budgeting-basics',
                'title' => 'Budgeting Basics',
                'description' => 'Plan your money before you spend it.',
                'icon' => 'bi-calculator',
                'color' => '#0EA5E9',
                'questions' => [
                    ['What is a budget?',
                        ['A plan for how to use your money', 'A type of coin', 'A shop discount', 'A bank building'],
                        0, 'A budget is a plan for how much to spend, save and share.'],
                    ['You get ৳500 a week. You spend ৳300 and save ৳200. What percentage are you saving?',
                        ['20%', '40%', '50%', '60%'],
                        1, '৳200 out of ৳500 is 40%.'],
                    ['Your daily spending limit is ৳100 and you have spent ৳85. How much more can you spend today?',
                        ['৳5', '৳15', '৳85', '৳185'],
                        1, '৳100 − ৳85 = ৳15 left for today.'],
                    ['Why is it useful to track your spending?',
                        ['To see where your money goes', 'To make shopping slower', 'Because it is the law', 'It is not useful'],
                        0, 'Tracking shows patterns, like spending most on snacks, so you can change your habits.'],
                    ['If you keep spending more than you receive, you will...',
                        ['Have extra money', 'Run out of money', 'Earn interest', 'Get a reward'],
                        1, 'Spending more than you get means your balance keeps going down until it is empty.'],
                ],
            ],
            [
                'slug' => 'money-safety',
                'title' => 'Money Safety',
                'description' => 'Stay safe with digital money like bKash and Nagad.',
                'icon' => 'bi-shield-lock',
                'color' => '#16A34A',
                'questions' => [
                    ['Someone calls saying they are from bKash and asks for your PIN. What should you do?',
                        ['Tell them the PIN', 'Hang up and tell a parent', 'Send them money', 'Post the PIN online'],
                        1, 'Real companies NEVER ask for your PIN. Hang up and tell a trusted adult.'],
                    ['An OTP (one-time password) sent to your phone should be...',
                        ['Shared with anyone who asks', 'Kept secret', 'Posted on social media', 'Sent to your friends'],
                        1, 'An OTP proves it is really you. Sharing it lets someone else take your money.'],
                    ['Which is the strongest PIN?',
                        ['1234', '0000', 'Your birth year', 'A random number only you know'],
                        3, 'Easy PINs like 1234 or birthdays are the first ones thieves try.'],
                    ['A message says "You won ৳10,000! Send ৳500 to claim it." This is most likely...',
                        ['A real prize', 'A scam', 'A gift from school', 'A bank bonus'],
                        1, 'If you have to pay money to get a prize, it is a scam.'],
                    ['You lost the phone that has your wallet app. What should happen first?',
                        ['Wait a few days', 'Ask a parent to lock your wallet', 'Buy a new phone', 'Do nothing'],
                        1, 'Locking the wallet stops anyone from spending your money, just like the emergency lock in Kids Wallet!'],
                ],
            ],
        ];
    }
}