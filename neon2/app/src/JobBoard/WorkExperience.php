<?php
namespace Neon2\JobBoard;

enum WorkExperience: string {
    case Intern = 'intern';
    case Junior = 'junior';
    case MidLevel = 'mid-level';
    case Senior = 'senior';
    case Lead = 'lead';
    case Manager = 'manager';
    case NotProvided = 'not-provided';
}
